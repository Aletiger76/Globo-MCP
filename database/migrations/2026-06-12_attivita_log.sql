-- Migrazione: tabella attivita_log (audit delle attività principali).
--
-- DA ESEGUIRE IN PRODUZIONE una volta, sul DB di Globo (MYSQL_DB_GLOBO),
-- prima o insieme al deploy della versione che introduce l'audit log (1.4.0).
-- Idempotente (IF NOT EXISTS): rieseguirla non fa danni.
--
-- Scritta da globo_log_attivita() (varie/funzioni.php) su login, creazioni
-- (progetto/nota/impegno) ed eliminazioni (tutti gli handler *_el).
--
-- RICORDA anche: registrare il cron giornaliero per
-- script/purga_log_attivita.php (retention audit log: 12 mesi).

CREATE TABLE IF NOT EXISTS `attivita_log` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `dt` timestamp NOT NULL DEFAULT current_timestamp(),
  `nome_utente` varchar(50) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `azione` varchar(40) NOT NULL,
  `entita` varchar(40) NOT NULL,
  `entita_id` int DEFAULT NULL,
  `dettaglio` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dt` (`dt`),
  KEY `idx_utente` (`nome_utente`),
  KEY `idx_entita` (`entita`,`entita_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
