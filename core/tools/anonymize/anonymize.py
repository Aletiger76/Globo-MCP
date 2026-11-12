#!/usr/bin/env python3
"""
Script di anonimizzazione del database Globo.

Segue le strategie definite in docs/pii-map.md. Ogni colonna sensibile
viene trattata con la strategia corrispondente; le colonne NON_SENSIBILE
e hash_o_mantieni non vengono toccate.

Uso:
    python anonymize.py [--dry-run] [--force] [--yes]

Variabili d'ambiente:
    DB_HOST      host del database          (default: 127.0.0.1)
    DB_PORT      porta del database         (default: 33306)
    DB_USER      utente del database        (default: root)
    DB_PASSWORD  password del database      (default: '')
    DB_NAME      nome del database          (default: globo_kosmosol)

Flag:
    --dry-run    mostra il conteggio delle righe senza eseguire UPDATE
    --force      bypassa il controllo di esecuzione precedente
    --yes        salta la conferma interattiva (per uso automatizzato)

Note sull'idempotenza:
    - fake_email, tronca_o_offusca, svuota, svuota_null, fake_ip_documentazione:
      completamente idempotenti (rilevano valori già anonimizzati e li saltano).
    - fake_username: idempotente strutturalmente (non rompe nulla), ma una seconda
      esecuzione ri-anonimizza i valori già fittizi poiché il mapping viene
      ricostruito dai valori correnti di utente.nome_utente. Usare --force
      consapevolmente se si riesegue.
    - fake_nome, fake_azienda, fake_telefono e simili: non idempotenti per design
      (Faker genera valori casuali ad ogni esecuzione).
"""

import argparse
import hashlib
import os
import sys
from datetime import datetime
from typing import Dict, List, Optional, Tuple

import pymysql
import pymysql.cursors
from faker import Faker

# ---------------------------------------------------------------------------
# Versione e costanti
# ---------------------------------------------------------------------------

SCRIPT_VERSION = '1.1.0'
TABELLA_MARCATORE = '_anonimization_done'  # tabella sentinella nel DB
MARKER_OFFUSCATO = '[anonimizzato]'
EMAIL_DOMAIN = 'example.invalid'
IP_PREFIX = '192.0.2'

# Codici provincia italiani (sigla a 2 lettere)
PROVINCE_IT = [
    'AG', 'AL', 'AN', 'AO', 'AR', 'AP', 'AT', 'AV', 'BA', 'BT', 'BL', 'BN',
    'BG', 'BI', 'BO', 'BZ', 'BS', 'BR', 'CA', 'CL', 'CB', 'CE', 'CT', 'CZ',
    'CH', 'CO', 'CS', 'CR', 'KR', 'CN', 'EN', 'FM', 'FE', 'FI', 'FG', 'FC',
    'FR', 'GE', 'GO', 'GR', 'IM', 'IS', 'SP', 'LT', 'LE', 'LC', 'LI', 'LO',
    'LU', 'MC', 'MN', 'MS', 'MT', 'ME', 'MI', 'MO', 'MB', 'NA', 'NO', 'NU',
    'OR', 'PD', 'PA', 'PR', 'PV', 'PG', 'PU', 'PE', 'PC', 'PI', 'PT', 'PN',
    'PZ', 'PO', 'RG', 'RA', 'RC', 'RE', 'RI', 'RN', 'RM', 'RO', 'SA', 'SS',
    'SV', 'SI', 'SR', 'SO', 'TA', 'TE', 'TR', 'TO', 'TP', 'TN', 'TV', 'TS',
    'UD', 'VA', 'VE', 'VB', 'VC', 'VR', 'VV', 'VI', 'VT',
]

REGIONI_IT = [
    'Abruzzo', 'Basilicata', 'Calabria', 'Campania', 'Emilia-Romagna',
    'Friuli-Venezia Giulia', 'Lazio', 'Liguria', 'Lombardia', 'Marche',
    'Molise', 'Piemonte', 'Puglia', 'Sardegna', 'Sicilia', 'Toscana',
    'Trentino-Alto Adige', 'Umbria', "Valle d'Aosta", 'Veneto',
]

# ---------------------------------------------------------------------------
# Configurazione e connessione
# ---------------------------------------------------------------------------

def leggi_configurazione() -> dict:
    """Legge i parametri di connessione da variabili d'ambiente."""
    return {
        'host':     os.getenv('DB_HOST', '127.0.0.1'),
        'port':     int(os.getenv('DB_PORT', '33306')),
        'user':     os.getenv('DB_USER', 'root'),
        'password': os.getenv('DB_PASSWORD', ''),
        'database': os.getenv('DB_NAME', 'globo_kosmosol'),
        'charset':  'latin1',
        'autocommit': False,
        'connect_timeout': 10,
    }


def connetti_db(config: dict) -> pymysql.connections.Connection:
    """Apre la connessione al database; interrompe lo script in caso di errore."""
    try:
        conn = pymysql.connect(**config)
        return conn
    except pymysql.Error as e:
        print(f'[ERRORE FATALE] Impossibile connettersi al database: {e}')
        sys.exit(1)

# ---------------------------------------------------------------------------
# Safety check: esecuzione precedente
# ---------------------------------------------------------------------------

def verifica_esecuzione_precedente(conn, force: bool) -> None:
    """
    Controlla se il DB è già stato anonimizzato (tabella sentinella presente).
    Se sì e --force non è specificato, termina con codice 1.
    Se --force è specificato, avvisa e prosegue.
    """
    with conn.cursor() as cur:
        cur.execute(
            'SELECT COUNT(*) FROM information_schema.tables '
            'WHERE table_schema = DATABASE() AND table_name = %s',
            (TABELLA_MARCATORE,),
        )
        tabella_esiste = cur.fetchone()[0] > 0

    if not tabella_esiste:
        return  # prima esecuzione: tutto ok

    # Recupera l'ultimo record di completamento
    with conn.cursor() as cur:
        cur.execute(
            f'SELECT ts, script_version, righe_totali '
            f'FROM `{TABELLA_MARCATORE}` ORDER BY id DESC LIMIT 1',
        )
        riga = cur.fetchone()

    print('[WARN] Il database risulta già anonimizzato.')
    if riga:
        ts, versione, righe = riga
        print(f'       Data/ora esecuzione precedente : {ts}')
        print(f'       Versione script usata          : {versione}')
        print(f'       Righe modificate               : {righe}')
    else:
        print(f'       (tabella {TABELLA_MARCATORE} presente ma vuota)')

    if not force:
        print('[ERRORE] Usa --force per rieseguire nonostante l\'esecuzione precedente.')
        sys.exit(1)

    print('[WARN] --force specificato: l\'anonimizzazione verrà rieseguita.')


def chiedi_conferma(yes: bool) -> None:
    """
    Chiede conferma interattiva prima di avviare le modifiche.
    Salta il prompt se --yes è passato (per ambienti automatizzati).
    """
    if yes:
        return
    print()
    print('[ATTENZIONE] Stai per modificare irreversibilmente i dati del database.')
    print('             Assicurati di avere un backup prima di procedere.')
    try:
        risposta = input('Confermi? [s/N] ').strip().lower()
    except (EOFError, KeyboardInterrupt):
        print('\n[ANNULLATO]')
        sys.exit(0)
    if risposta not in ('s', 'si', 'sì'):
        print('[ANNULLATO] Operazione annullata dall\'utente.')
        sys.exit(0)


def registra_completamento(conn, stats: 'Statistiche') -> None:
    """
    Crea la tabella sentinella (se non esiste) e inserisce il record di completamento.
    Chiamata solo al termine di un'esecuzione reale (non dry-run).
    """
    righe_totali = sum(stats.per_tabella.values())
    with conn.cursor() as cur:
        cur.execute(f"""
            CREATE TABLE IF NOT EXISTS `{TABELLA_MARCATORE}` (
                `id`             INT          NOT NULL AUTO_INCREMENT,
                `ts`             DATETIME     NOT NULL,
                `script_version` VARCHAR(20)  NOT NULL,
                `righe_totali`   INT          NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1
        """)
        cur.execute(
            f'INSERT INTO `{TABELLA_MARCATORE}` (ts, script_version, righe_totali) '
            f'VALUES (NOW(), %s, %s)',
            (SCRIPT_VERSION, righe_totali),
        )
    conn.commit()
    ts_ora = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    print(f'\n[INFO] Completamento registrato in `{TABELLA_MARCATORE}` ({ts_ora}).')

# ---------------------------------------------------------------------------
# Istanza globale Faker (it_IT)
# ---------------------------------------------------------------------------

_fake = Faker('it_IT')

# ---------------------------------------------------------------------------
# Generatori deterministici
# ---------------------------------------------------------------------------

def _seed_da_stringa(valore: str) -> int:
    """Calcola un seed intero da una stringa tramite MD5."""
    raw = valore.encode('latin1', errors='replace')
    return int(hashlib.md5(raw).hexdigest(), 16) % (2 ** 31)


def genera_username_fittizio(username_originale: str) -> str:
    """
    Genera un username nel formato <iniziale>.<cognome_italiano> in modo
    deterministico: lo stesso input produce sempre lo stesso output (NT-1).
    """
    fake_det = Faker('it_IT')
    fake_det.seed_instance(_seed_da_stringa(username_originale))
    cognome = fake_det.last_name().lower().replace(' ', '').replace("'", '')
    iniziale = username_originale[0].lower() if username_originale else 'x'
    return f'{iniziale}.{cognome}'


def ip_fittizio(ip_originale: str) -> str:
    """
    Genera un IP nel range 192.0.2.0/24 (RFC 5737) in modo deterministico.
    Stesso IP originale → stesso IP fittizio (NT-4).
    """
    ultimo_ottetto = (_seed_da_stringa(ip_originale) % 254) + 1
    return f'{IP_PREFIX}.{ultimo_ottetto}'


def piva_fittizia() -> str:
    """
    Genera una Partita IVA italiana fittizia (11 cifre).
    Faker it_IT restituisce il formato 'IT12345678901'; viene rimosso il prefisso 'IT'.
    """
    vat = _fake.vat_id()
    return vat[2:] if vat.upper().startswith('IT') else vat

# ---------------------------------------------------------------------------
# Mapping globale username (NT-1 di pii-map.md)
# ---------------------------------------------------------------------------

def costruisci_mapping_username(conn) -> Dict[str, str]:
    """
    Legge tutti i valori distinti da utente.nome_utente e costruisce
    il dizionario deterministico {username_originale: username_fittizio}.
    Il mapping viene costruito una volta sola e riutilizzato in tutte
    le 17 colonne delle 15 tabelle interessate.
    """
    mapping: Dict[str, str] = {}
    with conn.cursor() as cur:
        cur.execute(
            "SELECT DISTINCT nome_utente FROM utente "
            "WHERE nome_utente IS NOT NULL AND nome_utente != ''",
        )
        righe = cur.fetchall()
    for (username,) in righe:
        if username:
            mapping[username] = genera_username_fittizio(username)
    return mapping

# ---------------------------------------------------------------------------
# Raccolta statistiche
# ---------------------------------------------------------------------------

class Statistiche:
    """Accumula i contatori di righe per il riepilogo finale."""

    def __init__(self) -> None:
        self.per_tabella: Dict[str, int] = {}
        self.per_strategia: Dict[str, int] = {}
        self.errori: List[str] = []

    def registra(self, tabella: str, strategia: str, n: int) -> None:
        self.per_tabella[tabella] = self.per_tabella.get(tabella, 0) + n
        self.per_strategia[strategia] = self.per_strategia.get(strategia, 0) + n

    def registra_errore(self, msg: str) -> None:
        self.errori.append(msg)

    def stampa_riepilogo(self, dry_run: bool) -> None:
        titolo = 'RIEPILOGO DRY-RUN' if dry_run else 'RIEPILOGO ANONIMIZZAZIONE'
        print(f'\n{"=" * 60}')
        print(titolo)
        print('=' * 60)
        print('\nRighe per tabella:')
        for tabella, n in sorted(self.per_tabella.items()):
            print(f'  {tabella}: {n}')
        print('\nRighe per strategia:')
        for strategia, n in sorted(self.per_strategia.items()):
            print(f'  {strategia}: {n}')
        if self.errori:
            print(f'\nErrori ({len(self.errori)}):')
            for e in self.errori:
                print(f'  {e}')
        print('=' * 60)

# ---------------------------------------------------------------------------
# Funzione di log
# ---------------------------------------------------------------------------

def _log_ok(tabella: str, colonna: str, strategia: str, n: int, dry_run: bool) -> None:
    prefisso = '[DRY-RUN]' if dry_run else '[OK]     '
    if n == 0:
        print(f'[SKIP]    {tabella}.{colonna} ({strategia}): nessuna riga da aggiornare')
    else:
        print(f'{prefisso} {tabella}.{colonna} ({strategia}): {n} righe')

# ---------------------------------------------------------------------------
# Generazione valore fittizio per strategie per-riga
# ---------------------------------------------------------------------------

def _genera_valore_per_riga(strategia: str, valore_corrente: Optional[str]) -> Optional[str]:
    """
    Restituisce il valore fittizio per le strategie che producono
    un valore diverso per ogni riga tramite Faker.
    """
    if strategia == 'fake_nome':
        return _fake.name()
    if strategia == 'fake_azienda':
        return _fake.company()
    if strategia == 'fake_telefono':
        return _fake.phone_number()
    if strategia == 'fake_via':
        return _fake.street_address()
    if strategia == 'fake_indirizzo':
        # Indirizzo monoblocco (via + città): usato se il campo è un unico campo aggregato
        return _fake.address().replace('\n', ', ')
    if strategia == 'fake_cap':
        return _fake.postcode()
    if strategia == 'fake_citta':
        return _fake.city()
    if strategia == 'fake_provincia':
        return _fake.random_element(PROVINCE_IT)
    if strategia == 'fake_regione':
        return _fake.random_element(REGIONI_IT)
    if strategia == 'fake_cf':
        return _fake.ssn()
    if strategia == 'fake_piva':
        return piva_fittizia()
    return None

# ---------------------------------------------------------------------------
# Applicazione strategia su singola colonna
# ---------------------------------------------------------------------------

def processa_colonna(
    conn,
    tabella: str,
    colonna: str,
    strategia: str,
    pk_col: str,
    pk_tipo: str,
    mapping_username: Dict[str, str],
    stats: Statistiche,
    dry_run: bool,
) -> int:
    """
    Applica la strategia di anonimizzazione alla colonna indicata.
    Restituisce il numero di righe modificate (o che lo sarebbero in dry-run).
    In caso di errore non fatale, logga e restituisce 0.
    """
    try:
        return _applica_strategia(
            conn, tabella, colonna, strategia,
            pk_col, pk_tipo, mapping_username, dry_run,
        )
    except pymysql.Error as e:
        msg = f'{tabella}.{colonna} [{strategia}]: {e}'
        print(f'[ERRORE]  {msg}')
        stats.registra_errore(msg)
        try:
            conn.rollback()
        except Exception:
            pass
        return 0


def _applica_strategia(
    conn,
    tabella: str,
    colonna: str,
    strategia: str,
    pk_col: str,
    pk_tipo: str,
    mapping_username: Dict[str, str],
    dry_run: bool,
) -> int:
    """Implementazione interna del dispatcher di strategia."""

    # Strategie che non modificano nulla
    if strategia in ('mantieni', 'hash_o_mantieni'):
        return 0

    with conn.cursor() as cur:

        # --- tronca_o_offusca ---
        if strategia == 'tronca_o_offusca':
            if dry_run:
                cur.execute(
                    f'SELECT COUNT(*) FROM `{tabella}` WHERE `{colonna}` != %s',
                    (MARKER_OFFUSCATO,),
                )
                n = cur.fetchone()[0]
            else:
                cur.execute(
                    f'UPDATE `{tabella}` SET `{colonna}` = %s WHERE `{colonna}` != %s',
                    (MARKER_OFFUSCATO, MARKER_OFFUSCATO),
                )
                n = cur.rowcount
                conn.commit()
            _log_ok(tabella, colonna, strategia, n, dry_run)
            return n

        # --- svuota (colonna NOT NULL → stringa vuota) ---
        if strategia == 'svuota':
            if dry_run:
                cur.execute(f"SELECT COUNT(*) FROM `{tabella}` WHERE `{colonna}` != ''")
                n = cur.fetchone()[0]
            else:
                cur.execute(f"UPDATE `{tabella}` SET `{colonna}` = '' WHERE `{colonna}` != ''")
                n = cur.rowcount
                conn.commit()
            _log_ok(tabella, colonna, strategia, n, dry_run)
            return n

        # --- svuota_null (colonna nullable → NULL) ---
        if strategia == 'svuota_null':
            if dry_run:
                cur.execute(
                    f'SELECT COUNT(*) FROM `{tabella}` WHERE `{colonna}` IS NOT NULL',
                )
                n = cur.fetchone()[0]
            else:
                cur.execute(
                    f'UPDATE `{tabella}` SET `{colonna}` = NULL WHERE `{colonna}` IS NOT NULL',
                )
                n = cur.rowcount
                conn.commit()
            _log_ok(tabella, colonna, strategia, n, dry_run)
            return n

        # --- fake_email ---
        # Idempotente: salta righe già nel formato *@example.invalid
        if strategia == 'fake_email':
            filtro = f'%@{EMAIL_DOMAIN}'
            if dry_run:
                cur.execute(
                    f'SELECT COUNT(*) FROM `{tabella}` WHERE `{colonna}` NOT LIKE %s',
                    (filtro,),
                )
                n = cur.fetchone()[0]
            else:
                cur.execute(
                    f"UPDATE `{tabella}` "
                    f"SET `{colonna}` = CONCAT('utente_', `{pk_col}`, '@{EMAIL_DOMAIN}') "
                    f"WHERE `{colonna}` NOT LIKE %s",
                    (filtro,),
                )
                n = cur.rowcount
                conn.commit()
            _log_ok(tabella, colonna, strategia, n, dry_run)
            return n

        # --- fake_username ---
        # Mapping deterministico globale (NT-1). Aggiornamento valore per valore
        # per compatibilità con colonne PK varchar (utente.nome_utente).
        if strategia == 'fake_username':
            if not mapping_username:
                _log_ok(tabella, colonna, strategia, 0, dry_run)
                return 0
            valori_originali = list(mapping_username.keys())
            placeholders = ', '.join(['%s'] * len(valori_originali))
            if dry_run:
                cur.execute(
                    f'SELECT COUNT(*) FROM `{tabella}` WHERE `{colonna}` IN ({placeholders})',
                    valori_originali,
                )
                n = cur.fetchone()[0]
            else:
                n = 0
                for originale, fittizio in mapping_username.items():
                    cur.execute(
                        f'UPDATE `{tabella}` SET `{colonna}` = %s WHERE `{colonna}` = %s',
                        (fittizio, originale),
                    )
                    n += cur.rowcount
                conn.commit()
            _log_ok(tabella, colonna, strategia, n, dry_run)
            return n

        # --- fake_ip_documentazione ---
        # Basato su valori distinti (non richiede PK): idempotente per IP già in 192.0.2.x
        if strategia == 'fake_ip_documentazione':
            filtro_fittizi = f'{IP_PREFIX}.%'
            cur.execute(
                f'SELECT DISTINCT `{colonna}` FROM `{tabella}` '
                f'WHERE `{colonna}` NOT LIKE %s AND `{colonna}` IS NOT NULL',
                (filtro_fittizi,),
            )
            ip_distinti = [r[0] for r in cur.fetchall() if r[0]]
            if dry_run:
                cur.execute(
                    f'SELECT COUNT(*) FROM `{tabella}` WHERE `{colonna}` NOT LIKE %s',
                    (filtro_fittizi,),
                )
                n = cur.fetchone()[0]
            else:
                n = 0
                for ip_orig in ip_distinti:
                    cur.execute(
                        f'UPDATE `{tabella}` SET `{colonna}` = %s WHERE `{colonna}` = %s',
                        (ip_fittizio(ip_orig), ip_orig),
                    )
                    n += cur.rowcount
                conn.commit()
            _log_ok(tabella, colonna, strategia, n, dry_run)
            return n

    # --- Strategie per-riga (Faker, valore diverso per ogni record) ---
    if strategia in (
        'fake_nome', 'fake_azienda', 'fake_telefono',
        'fake_via', 'fake_indirizzo', 'fake_cap', 'fake_citta',
        'fake_provincia', 'fake_regione', 'fake_cf', 'fake_piva',
    ):
        return _processa_per_riga(conn, tabella, colonna, strategia, pk_col, dry_run)

    print(f'[WARN]    {tabella}.{colonna}: strategia "{strategia}" non implementata, saltata')
    return 0


def _processa_per_riga(
    conn,
    tabella: str,
    colonna: str,
    strategia: str,
    pk_col: str,
    dry_run: bool,
) -> int:
    """
    Aggiorna ogni riga con un valore fittizio generato da Faker.
    Necessario per strategie che producono valori diversi record per record.
    Esegue commit ogni BATCH righe per limitare l'uso di memoria transazionale.
    """
    BATCH = 500

    with conn.cursor() as cur:
        cur.execute(f'SELECT `{pk_col}`, `{colonna}` FROM `{tabella}`')
        righe = cur.fetchall()

    n = len(righe)

    if dry_run:
        _log_ok(tabella, colonna, strategia, n, dry_run)
        return n

    with conn.cursor() as cur:
        for i, (pk_val, valore_corrente) in enumerate(righe):
            valore_fittizio = _genera_valore_per_riga(strategia, valore_corrente)
            if valore_fittizio is None:
                continue
            # Encoding difensivo: scarta caratteri non rappresentabili in latin1
            try:
                valore_fittizio.encode('latin1')
            except UnicodeEncodeError:
                valore_fittizio = valore_fittizio.encode(
                    'latin1', errors='replace',
                ).decode('latin1')
            cur.execute(
                f'UPDATE `{tabella}` SET `{colonna}` = %s WHERE `{pk_col}` = %s',
                (valore_fittizio, pk_val),
            )
            if (i + 1) % BATCH == 0:
                conn.commit()

    conn.commit()
    _log_ok(tabella, colonna, strategia, n, dry_run)
    return n

# ---------------------------------------------------------------------------
# Configurazione completa: tabelle, PK, colonne e strategie
# ---------------------------------------------------------------------------
#
# Struttura di ogni voce:
#   (nome_tabella, colonna_pk, tipo_pk, [(colonna, strategia), ...])
#
# tipo_pk: 'int' per chiavi numeriche, 'varchar' per chiavi testuali.
#
# Ordine di esecuzione per NT-2 (pii-map.md):
#   1. utente       — sorgente del mapping username; email prima della PK
#   2. contatto     — sorgente principale PII clienti
#   3. altre tabelle (ordine alfabetico)
#   4. nl_log_invio_2009 … nl_log_invio_2019

TABELLE_CONFIG: List[Tuple] = [

    # ── 1. utente ───────────────────────────────────────────────────────────
    # email aggiornata PRIMA di nome_utente (la PK è usata nel CONCAT email).
    ('utente', 'nome_utente', 'varchar', [
        ('email',            'fake_email'),
        ('auth_token_login', 'svuota_null'),   # nullable → NULL
        ('nome_utente',      'fake_username'),  # PK varchar — aggiornata per ultima
    ]),

    # ── 2. contatto ─────────────────────────────────────────────────────────
    ('contatto', 'id', 'int', [
        ('azienda',            'fake_azienda'),
        ('nome',               'fake_nome'),
        ('cognome',            'fake_nome'),
        ('telefono',           'fake_telefono'),
        ('cellulare',          'fake_telefono'),
        ('fax',                'fake_telefono'),
        ('email',              'fake_email'),
        ('indirizzo',          'fake_via'),
        ('cap',                'fake_cap'),
        ('citta',              'fake_citta'),
        ('provincia',          'fake_provincia'),
        ('regione',            'fake_regione'),
        ('pi',                 'fake_piva'),
        ('cf',                 'fake_cf'),
        ('note',               'tronca_o_offusca'),
        ('nl_codice_conferma', 'svuota'),        # NOT NULL → ''
    ]),

    # ── 3. Altre tabelle (ordine alfabetico) ────────────────────────────────
    ('bilancio', 'id', 'int', [
        ('descrizione',            'tronca_o_offusca'),
        ('utente_ultima_modifica', 'fake_username'),
    ]),

    ('bilanciov2', 'id', 'int', [
        ('cliente_fornitore',      'fake_azienda'),
        ('email',                  'fake_email'),
        ('dati_fatturazione',      'tronca_o_offusca'),
        ('note_fattura',           'tronca_o_offusca'),
        ('note',                   'tronca_o_offusca'),
        ('utente_ultima_modifica', 'fake_username'),
    ]),

    ('bilanciov2_agente', 'id', 'int', [
        ('nome',  'fake_nome'),       # agente: persona fisica
        ('email', 'fake_email'),
        ('note',  'tronca_o_offusca'),
    ]),

    ('bilanciov2_dettaglio', 'id', 'int', [
        ('descrizione',            'tronca_o_offusca'),
        ('utente_ultima_modifica', 'fake_username'),
    ]),

    ('globomail', 'id', 'int', [
        ('utente_mitt', 'fake_username'),
        ('utente_dest', 'fake_username'),
        ('oggetto',     'tronca_o_offusca'),
        ('testo',       'tronca_o_offusca'),
    ]),

    ('impegno', 'id', 'int', [
        ('descrizione',            'tronca_o_offusca'),
        ('responsabile',           'fake_username'),
        ('utente_ultima_modifica', 'fake_username'),
    ]),

    # login: nessuna PK dichiarata. fake_username e fake_ip_documentazione
    # sono entrambe value-based e non richiedono la PK.
    ('login', 'nome_utente', 'varchar', [
        ('nome_utente', 'fake_username'),
        ('ip',          'fake_ip_documentazione'),
    ]),

    # login_old: nessuna PK dichiarata. fake_username è value-based.
    ('login_old', 'nome_utente', 'varchar', [
        ('nome_utente', 'fake_username'),
    ]),

    ('nl_template', 'id', 'int', [
        ('header', 'tronca_o_offusca'),
        ('body',   'tronca_o_offusca'),
        ('footer', 'tronca_o_offusca'),
    ]),

    ('progetto', 'id', 'int', [
        ('nome',        'fake_azienda'),   # spesso cita nomi di clienti/aziende
        ('descrizione', 'tronca_o_offusca'),
    ]),

    ('progetto_documento', 'id', 'int', [
        ('nome',          'tronca_o_offusca'),
        ('file',          'tronca_o_offusca'),
        ('utente_upload', 'fake_username'),
    ]),

    ('progetto_nota', 'id', 'int', [
        ('utente_creazione',       'fake_username'),
        ('descrizione',            'tronca_o_offusca'),
        ('utente_ultima_modifica', 'fake_username'),
    ]),

    # PK composita: fake_username è value-based, pk_col non usato per questa strategia.
    ('progetto_procedura_step_chiuso', 'id_progetto', 'int', [
        ('utente', 'fake_username'),
    ]),

    ('progetto_procedura_step_non_attivo', 'id_progetto', 'int', [
        ('utente', 'fake_username'),
    ]),

    ('scadenza', 'id', 'int', [
        ('descrizione',            'tronca_o_offusca'),
        ('responsabile',           'fake_username'),
        ('utente_ultima_modifica', 'fake_username'),
    ]),
]

# ── 4. Tabelle nl_log_invio_AAAA (2009-2019) — al fondo per NT-2 ──────────
# errore: text NOT NULL → svuota (stringa vuota, non NULL)
for _anno in range(2009, 2020):
    TABELLE_CONFIG.append((
        f'nl_log_invio_{_anno}', 'id', 'int', [
            ('email',  'fake_email'),
            ('errore', 'svuota'),
        ],
    ))

# ---------------------------------------------------------------------------
# Orchestrazione principale
# ---------------------------------------------------------------------------

def main() -> None:
    parser = argparse.ArgumentParser(
        description='Anonimizza il database Globo secondo le strategie di pii-map.md',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog=(
            'Esempi:\n'
            '  python anonymize.py --dry-run          # simula senza modificare\n'
            '  python anonymize.py --yes               # esegue senza conferma\n'
            '  python anonymize.py --force --yes       # riesegue forzatamente\n'
        ),
    )
    parser.add_argument(
        '--dry-run',
        action='store_true',
        help='Simula senza modificare il DB: mostra il conteggio delle righe',
    )
    parser.add_argument(
        '--force',
        action='store_true',
        help='Bypassa il controllo di esecuzione precedente',
    )
    parser.add_argument(
        '--yes',
        action='store_true',
        help='Salta la conferma interattiva (per uso automatizzato)',
    )
    args = parser.parse_args()

    dry_run: bool = args.dry_run
    force: bool   = args.force
    yes: bool     = args.yes

    config = leggi_configurazione()

    # Banner iniziale: riepilogo visivo prima di qualsiasi azione
    n_colonne = sum(len(colonne) for _, _, _, colonne in TABELLE_CONFIG)
    modalita: list = []
    if dry_run:
        modalita.append('DRY-RUN  — simulazione, nessun dato modificato')
    if force:
        modalita.append('FORCE    — bypassa controllo esecuzione precedente')
    if yes:
        modalita.append('YES      — conferma automatica (no prompt)')
    if not modalita:
        modalita.append('standard — interattiva, dati modificati')

    sep = '=' * 64
    print(sep)
    print(f'  Anonimizzazione Globo — script v{SCRIPT_VERSION}')
    print(sep)
    print(f'  Azione    : sostituzione PII secondo docs/pii-map.md')
    print(f'  Database  : {config["host"]}:{config["port"]} / {config["database"]}')
    print(f'  Colonne   : {n_colonne} configurate in TABELLE_CONFIG')
    for i, m in enumerate(modalita):
        etichetta = 'Modalità  :' if i == 0 else '           '
        print(f'  {etichetta} {m}')
    print(sep)
    print()

    conn = connetti_db(config)

    try:
        # Safety check 1: esecuzione precedente
        verifica_esecuzione_precedente(conn, force)

        # Safety check 2: conferma interattiva (solo in modalità reale)
        if not dry_run:
            chiedi_conferma(yes)

        # Fase 1 — costruzione mapping username deterministico (NT-1)
        print('\n[INFO] Fase 1: costruzione mapping username...')
        mapping_username = costruisci_mapping_username(conn)
        print(f'[INFO] {len(mapping_username)} username distinti trovati in utente.nome_utente')

        stats = Statistiche()

        # Fase 2 — anonimizzazione nell'ordine NT-2
        print('\n[INFO] Fase 2: anonimizzazione colonne...')
        for (tabella, pk_col, pk_tipo, colonne) in TABELLE_CONFIG:
            print(f'\n  ── {tabella} ──')
            for (colonna, strategia) in colonne:
                n = processa_colonna(
                    conn=conn,
                    tabella=tabella,
                    colonna=colonna,
                    strategia=strategia,
                    pk_col=pk_col,
                    pk_tipo=pk_tipo,
                    mapping_username=mapping_username,
                    stats=stats,
                    dry_run=dry_run,
                )
                stats.registra(tabella, strategia, n)

        # Registra il completamento nel DB (solo esecuzione reale)
        if not dry_run:
            registra_completamento(conn, stats)

    except KeyboardInterrupt:
        print('\n[INTERROTTO] Operazione annullata dall\'utente.')
        conn.rollback()
        sys.exit(130)
    except pymysql.Error as e:
        print(f'\n[ERRORE FATALE] Connessione DB persa: {e}')
        sys.exit(1)
    finally:
        conn.close()

    stats.stampa_riepilogo(dry_run)

    if dry_run:
        print('\n[DRY-RUN] Nessuna modifica effettuata. Rimuovi --dry-run per applicare.')


if __name__ == '__main__':
    main()
