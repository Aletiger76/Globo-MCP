-- Inizializzazione DB per Globo MCP (eseguito una volta, alla creazione del volume).
-- I dump sono table-only (niente CREATE DATABASE): creiamo qui i database e l'utente
-- dev, poi i dump si importano a parte (vedi README.md).
--
-- Charset: NON forzato sui database (ereditano utf8mb4 dal server). Le tabelle
-- arrivano dai dump con il loro charset originale (latin1): replica la
-- configurazione "mista" legacy descritta in CLAUDE.md §7.

CREATE DATABASE IF NOT EXISTS `globo_mcprotection`;
-- DB secondario del modulo custom adhoc_mcp_* (MYSQL_DB_MCP nel config).
CREATE DATABASE IF NOT EXISTS `mcprotection`;

CREATE USER IF NOT EXISTS 'globomcp'@'%' IDENTIFIED BY 'globomcppass_dev';
GRANT ALL PRIVILEGES ON `globo_mcprotection`.* TO 'globomcp'@'%';
GRANT ALL PRIVILEGES ON `mcprotection`.* TO 'globomcp'@'%';
FLUSH PRIVILEGES;
