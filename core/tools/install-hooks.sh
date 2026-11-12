#!/usr/bin/env bash
#
# Installa gli hook Git di questo repo (vedi CLAUDE.md §10).
#
# PERCHE' ESISTE: gli hook vivono in .git/hooks/, che Git NON versiona. Si perdono a ogni
# nuovo clone, e la loro assenza e' silenziosa - un commit che passa liscio sembra identico a
# un commit verificato. E' cosi' che il controllo di sintassi PHP era rimasto fuori da due
# cloni in produzione senza che nulla lo segnalasse (constatato il 2026-09-16). La copia
# canonica sta sotto versione qui accanto, e questo script la mette al suo posto.
#
# USO (dalla radice del repo, in Git Bash):
#     bash tools/install-hooks.sh              # in Globo8
#     bash core/tools/install-hooks.sh         # in un clone (arriva col subtree del core)
#
# Da rieseguire dopo ogni clone del repo, e quando la copia canonica cambia.
#
# Se esiste gia' un hook DIVERSO da quello canonico non lo butta via: lo mette da parte in
# pre-commit.bak prima di sostituirlo.

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SORGENTE="$SCRIPT_DIR/pre-commit"

if [ ! -f "$SORGENTE" ]; then
  echo "ERRORE: copia canonica non trovata in $SORGENTE" >&2
  exit 1
fi

if ! git rev-parse --git-dir >/dev/null 2>&1; then
  echo "ERRORE: non sei dentro un repository Git." >&2
  exit 1
fi

HOOKS_DIR="$(git rev-parse --git-dir)/hooks"
DEST="$HOOKS_DIR/pre-commit"
mkdir -p "$HOOKS_DIR"

if [ -f "$DEST" ] && ! cmp -s "$SORGENTE" "$DEST"; then
  cp "$DEST" "$DEST.bak"
  echo "  hook preesistente e diverso: messo da parte in $(basename "$DEST").bak"
fi

cp "$SORGENTE" "$DEST"
chmod +x "$DEST"

echo "  pre-commit installato in $HOOKS_DIR"

# Verifica che sia davvero utilizzabile: un hook non eseguibile viene ignorato da Git
# in silenzio, che e' esattamente il modo in cui questi controlli spariscono.
if [ -x "$DEST" ]; then
  echo "  eseguibile: si"
else
  echo "  ATTENZIONE: il file NON risulta eseguibile, Git lo ignorera'." >&2
  exit 1
fi

CONTAINER=$(grep -m1 -E '^[[:space:]]*container_name:[[:space:]]*[A-Za-z0-9_-]+-web' docker-compose.yml 2>/dev/null \
            | sed -E 's/.*container_name:[[:space:]]*//' | tr -d '\r' | tr -d '[:space:]')
if [ -n "$CONTAINER" ]; then
  echo "  container web rilevato: $CONTAINER"
else
  echo "  ATTENZIONE: nessun container web rilevato da docker-compose.yml."
  echo "              Il check verra' saltato: vedi tools/pre-commit."
fi
