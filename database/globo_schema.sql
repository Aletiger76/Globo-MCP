-- MariaDB dump 10.19  Distrib 10.11.7-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: globo_kosmosol
-- ------------------------------------------------------
-- Server version	10.11.7-MariaDB-cll-lve

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bilancio`
--

DROP TABLE IF EXISTS `bilancio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bilancio` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` varchar(20) NOT NULL,
  `data` date NOT NULL,
  `descrizione` longtext NOT NULL,
  `fattura` varchar(5) NOT NULL,
  `importo` decimal(10,2) NOT NULL,
  `flag_esigibilita` tinyint(1) NOT NULL,
  `flag_pagamento` int(1) NOT NULL,
  `utente_ultima_modifica` varchar(50) NOT NULL DEFAULT '',
  `dt_ultima_modifica` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3113 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bilancio`
--

LOCK TABLES `bilancio` WRITE;
/*!40000 ALTER TABLE `bilancio` DISABLE KEYS */;
/*!40000 ALTER TABLE `bilancio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bilanciov2`
--

DROP TABLE IF EXISTS `bilanciov2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bilanciov2` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` enum('entrata','uscita') NOT NULL,
  `data` date NOT NULL,
  `cliente_fornitore` varchar(255) NOT NULL,
  `email` varchar(500) NOT NULL,
  `id_bilanciov2_tipo_cliente` int(3) NOT NULL,
  `dati_fatturazione` text NOT NULL,
  `fattura` varchar(10) NOT NULL,
  `modalita_pagamento` text NOT NULL,
  `banca` text NOT NULL,
  `note_fattura` text NOT NULL,
  `flag_esigibilita` tinyint(1) NOT NULL,
  `flag_pagamento` int(1) NOT NULL,
  `credito_debito_attuale` decimal(10,2) NOT NULL,
  `data_pagamento` date NOT NULL,
  `data_scadenza_pagamento` date NOT NULL,
  `flag_sollecito` tinyint(1) NOT NULL,
  `id_bilanciov2_agente` bigint(20) unsigned NOT NULL,
  `perc_provvigione` int(2) NOT NULL,
  `note` text NOT NULL,
  `utente_ultima_modifica` varchar(50) NOT NULL,
  `dt_ultima_modifica` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10138 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bilanciov2`
--

LOCK TABLES `bilanciov2` WRITE;
/*!40000 ALTER TABLE `bilanciov2` DISABLE KEYS */;
/*!40000 ALTER TABLE `bilanciov2` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bilanciov2_agente`
--

DROP TABLE IF EXISTS `bilanciov2_agente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bilanciov2_agente` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `note` longtext NOT NULL,
  `fatture_provvigioni_pagate` longtext NOT NULL,
  `provvigioni_pagate` longtext NOT NULL,
  `totale_provvigioni_pagate` decimal(10,2) NOT NULL,
  `dt_inserimento` datetime NOT NULL,
  `dt_ultima_modifica` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=216 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bilanciov2_agente`
--

LOCK TABLES `bilanciov2_agente` WRITE;
/*!40000 ALTER TABLE `bilanciov2_agente` DISABLE KEYS */;
/*!40000 ALTER TABLE `bilanciov2_agente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bilanciov2_categoria`
--

DROP TABLE IF EXISTS `bilanciov2_categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bilanciov2_categoria` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `tipo` enum('entrata','uscita') NOT NULL,
  `nome` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bilanciov2_categoria`
--

LOCK TABLES `bilanciov2_categoria` WRITE;
/*!40000 ALTER TABLE `bilanciov2_categoria` DISABLE KEYS */;
/*!40000 ALTER TABLE `bilanciov2_categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bilanciov2_dettaglio`
--

DROP TABLE IF EXISTS `bilanciov2_dettaglio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bilanciov2_dettaglio` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_bilanciov2` bigint(20) unsigned NOT NULL,
  `id_bilanciov2_categoria` varchar(50) NOT NULL,
  `descrizione` text NOT NULL,
  `flag_canone` tinyint(1) NOT NULL,
  `data_scadenza_canone` date NOT NULL,
  `flag_ricorda` tinyint(1) NOT NULL,
  `imponibile` decimal(10,2) NOT NULL,
  `iva` int(2) NOT NULL,
  `utente_ultima_modifica` varchar(50) NOT NULL,
  `dt_ultima_modifica` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14715 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bilanciov2_dettaglio`
--

LOCK TABLES `bilanciov2_dettaglio` WRITE;
/*!40000 ALTER TABLE `bilanciov2_dettaglio` DISABLE KEYS */;
/*!40000 ALTER TABLE `bilanciov2_dettaglio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bilanciov2_tipo_cliente`
--

DROP TABLE IF EXISTS `bilanciov2_tipo_cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bilanciov2_tipo_cliente` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bilanciov2_tipo_cliente`
--

LOCK TABLES `bilanciov2_tipo_cliente` WRITE;
/*!40000 ALTER TABLE `bilanciov2_tipo_cliente` DISABLE KEYS */;
/*!40000 ALTER TABLE `bilanciov2_tipo_cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contatto`
--

DROP TABLE IF EXISTS `contatto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contatto` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `azienda` varchar(255) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `cognome` varchar(255) NOT NULL,
  `qualifica` varchar(255) NOT NULL,
  `telefono` varchar(255) NOT NULL,
  `cellulare` varchar(255) NOT NULL,
  `fax` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sito` varchar(255) NOT NULL,
  `indirizzo` varchar(255) NOT NULL,
  `cap` varchar(255) NOT NULL,
  `citta` varchar(255) NOT NULL,
  `provincia` varchar(255) NOT NULL,
  `regione` varchar(255) NOT NULL,
  `area` varchar(255) NOT NULL,
  `nazione` varchar(255) NOT NULL,
  `pi` varchar(255) NOT NULL,
  `cf` varchar(255) NOT NULL,
  `note` longtext NOT NULL,
  `nl_flag_confermato` tinyint(1) NOT NULL DEFAULT 1,
  `nl_codice_conferma` varchar(255) NOT NULL,
  `dt_inserimento` datetime NOT NULL,
  `dt_ultima_modifica` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9005 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contatto`
--

LOCK TABLES `contatto` WRITE;
/*!40000 ALTER TABLE `contatto` DISABLE KEYS */;
/*!40000 ALTER TABLE `contatto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `globomail`
--

DROP TABLE IF EXISTS `globomail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `globomail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dt` datetime NOT NULL,
  `utente_mitt` varchar(50) NOT NULL,
  `utente_dest` varchar(50) NOT NULL,
  `oggetto` varchar(255) NOT NULL,
  `testo` longtext NOT NULL,
  `flag_lettura` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `globomail`
--

LOCK TABLES `globomail` WRITE;
/*!40000 ALTER TABLE `globomail` DISABLE KEYS */;
/*!40000 ALTER TABLE `globomail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `impegno`
--

DROP TABLE IF EXISTS `impegno`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `impegno` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL DEFAULT '0000-00-00',
  `ora` varchar(10) NOT NULL DEFAULT '',
  `durata` varchar(10) NOT NULL DEFAULT '',
  `descrizione` longtext NOT NULL,
  `responsabile` varchar(50) NOT NULL DEFAULT '',
  `utente_ultima_modifica` varchar(50) NOT NULL DEFAULT '',
  `dt_ultima_modifica` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10288 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `impegno`
--

LOCK TABLES `impegno` WRITE;
/*!40000 ALTER TABLE `impegno` DISABLE KEYS */;
/*!40000 ALTER TABLE `impegno` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login`
--

DROP TABLE IF EXISTS `login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login` (
  `nome_utente` varchar(50) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `accesso` tinyint(1) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login`
--

LOCK TABLES `login` WRITE;
/*!40000 ALTER TABLE `login` DISABLE KEYS */;
/*!40000 ALTER TABLE `login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attivita_log`
-- Audit log delle attività principali (login, creazioni, eliminazioni).
-- Scritta da globo_log_attivita() (varie/funzioni.php). Retention 12 mesi
-- via src/script/purga_log_attivita.php.
--

DROP TABLE IF EXISTS `attivita_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attivita_log` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `login_old`
--

DROP TABLE IF EXISTS `login_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_old` (
  `nome_utente` varchar(50) NOT NULL DEFAULT '',
  `dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_old`
--

LOCK TABLES `login_old` WRITE;
/*!40000 ALTER TABLE `login_old` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_old` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_lista`
--

DROP TABLE IF EXISTS `nl_lista`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_lista` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `flag_stato` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_lista`
--

LOCK TABLES `nl_lista` WRITE;
/*!40000 ALTER TABLE `nl_lista` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_lista` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_lista_contatto`
--

DROP TABLE IF EXISTS `nl_lista_contatto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_lista_contatto` (
  `id_lista` bigint(20) unsigned NOT NULL,
  `id_contatto` int(11) NOT NULL,
  `flag_stato` tinyint(1) NOT NULL DEFAULT 1,
  `dt_inserimento` datetime NOT NULL,
  PRIMARY KEY (`id_lista`,`id_contatto`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_lista_contatto`
--

LOCK TABLES `nl_lista_contatto` WRITE;
/*!40000 ALTER TABLE `nl_lista_contatto` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_lista_contatto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_log_invio_2009`
--

DROP TABLE IF EXISTS `nl_log_invio_2009`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_log_invio_2009` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_newsletter` bigint(20) unsigned NOT NULL,
  `id_lista` int(11) NOT NULL,
  `nome_lista` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dt_invio` datetime NOT NULL,
  `flag_invio_ok` tinyint(1) NOT NULL,
  `errore` text NOT NULL,
  `flag_apertura` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2324 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_log_invio_2009`
--

LOCK TABLES `nl_log_invio_2009` WRITE;
/*!40000 ALTER TABLE `nl_log_invio_2009` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_log_invio_2009` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_log_invio_2010`
--

DROP TABLE IF EXISTS `nl_log_invio_2010`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_log_invio_2010` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_newsletter` bigint(20) unsigned NOT NULL,
  `id_lista` int(11) NOT NULL,
  `nome_lista` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dt_invio` datetime NOT NULL,
  `flag_invio_ok` tinyint(1) NOT NULL,
  `errore` text NOT NULL,
  `flag_apertura` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3565 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_log_invio_2010`
--

LOCK TABLES `nl_log_invio_2010` WRITE;
/*!40000 ALTER TABLE `nl_log_invio_2010` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_log_invio_2010` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_log_invio_2011`
--

DROP TABLE IF EXISTS `nl_log_invio_2011`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_log_invio_2011` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_newsletter` bigint(20) unsigned NOT NULL,
  `id_lista` int(11) NOT NULL,
  `nome_lista` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dt_invio` datetime NOT NULL,
  `flag_invio_ok` tinyint(1) NOT NULL,
  `errore` text NOT NULL,
  `flag_apertura` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5103 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_log_invio_2011`
--

LOCK TABLES `nl_log_invio_2011` WRITE;
/*!40000 ALTER TABLE `nl_log_invio_2011` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_log_invio_2011` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_log_invio_2012`
--

DROP TABLE IF EXISTS `nl_log_invio_2012`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_log_invio_2012` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_newsletter` bigint(20) unsigned NOT NULL,
  `id_lista` int(11) NOT NULL,
  `nome_lista` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dt_invio` datetime NOT NULL,
  `flag_invio_ok` tinyint(1) NOT NULL,
  `errore` text NOT NULL,
  `flag_apertura` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7280 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_log_invio_2012`
--

LOCK TABLES `nl_log_invio_2012` WRITE;
/*!40000 ALTER TABLE `nl_log_invio_2012` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_log_invio_2012` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_log_invio_2013`
--

DROP TABLE IF EXISTS `nl_log_invio_2013`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_log_invio_2013` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_newsletter` bigint(20) unsigned NOT NULL,
  `id_lista` int(11) NOT NULL,
  `nome_lista` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dt_invio` datetime NOT NULL,
  `flag_invio_ok` tinyint(1) NOT NULL,
  `errore` text NOT NULL,
  `flag_apertura` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3441 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_log_invio_2013`
--

LOCK TABLES `nl_log_invio_2013` WRITE;
/*!40000 ALTER TABLE `nl_log_invio_2013` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_log_invio_2013` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_log_invio_2014`
--

DROP TABLE IF EXISTS `nl_log_invio_2014`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_log_invio_2014` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_newsletter` bigint(20) unsigned NOT NULL,
  `id_lista` int(11) NOT NULL,
  `nome_lista` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dt_invio` datetime NOT NULL,
  `flag_invio_ok` tinyint(1) NOT NULL,
  `errore` text NOT NULL,
  `flag_apertura` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7620 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_log_invio_2014`
--

LOCK TABLES `nl_log_invio_2014` WRITE;
/*!40000 ALTER TABLE `nl_log_invio_2014` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_log_invio_2014` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_log_invio_2015`
--

DROP TABLE IF EXISTS `nl_log_invio_2015`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_log_invio_2015` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_newsletter` bigint(20) unsigned NOT NULL,
  `id_lista` int(11) NOT NULL,
  `nome_lista` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dt_invio` datetime NOT NULL,
  `flag_invio_ok` tinyint(1) NOT NULL,
  `errore` text NOT NULL,
  `flag_apertura` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=691 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_log_invio_2015`
--

LOCK TABLES `nl_log_invio_2015` WRITE;
/*!40000 ALTER TABLE `nl_log_invio_2015` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_log_invio_2015` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_log_invio_2016`
--

DROP TABLE IF EXISTS `nl_log_invio_2016`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_log_invio_2016` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_newsletter` bigint(20) unsigned NOT NULL,
  `id_lista` int(11) NOT NULL,
  `nome_lista` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dt_invio` datetime NOT NULL,
  `flag_invio_ok` tinyint(1) NOT NULL,
  `errore` text NOT NULL,
  `flag_apertura` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_log_invio_2016`
--

LOCK TABLES `nl_log_invio_2016` WRITE;
/*!40000 ALTER TABLE `nl_log_invio_2016` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_log_invio_2016` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_log_invio_2017`
--

DROP TABLE IF EXISTS `nl_log_invio_2017`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_log_invio_2017` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_newsletter` bigint(20) unsigned NOT NULL,
  `id_lista` int(11) NOT NULL,
  `nome_lista` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dt_invio` datetime NOT NULL,
  `flag_invio_ok` tinyint(1) NOT NULL,
  `errore` text NOT NULL,
  `flag_apertura` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_log_invio_2017`
--

LOCK TABLES `nl_log_invio_2017` WRITE;
/*!40000 ALTER TABLE `nl_log_invio_2017` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_log_invio_2017` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_log_invio_2018`
--

DROP TABLE IF EXISTS `nl_log_invio_2018`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_log_invio_2018` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_newsletter` bigint(20) unsigned NOT NULL,
  `id_lista` int(11) NOT NULL,
  `nome_lista` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dt_invio` datetime NOT NULL,
  `flag_invio_ok` tinyint(1) NOT NULL,
  `errore` text NOT NULL,
  `flag_apertura` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_log_invio_2018`
--

LOCK TABLES `nl_log_invio_2018` WRITE;
/*!40000 ALTER TABLE `nl_log_invio_2018` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_log_invio_2018` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_log_invio_2019`
--

DROP TABLE IF EXISTS `nl_log_invio_2019`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_log_invio_2019` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_newsletter` bigint(20) unsigned NOT NULL,
  `id_lista` int(11) NOT NULL,
  `nome_lista` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dt_invio` datetime NOT NULL,
  `flag_invio_ok` tinyint(1) NOT NULL,
  `errore` text NOT NULL,
  `flag_apertura` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_log_invio_2019`
--

LOCK TABLES `nl_log_invio_2019` WRITE;
/*!40000 ALTER TABLE `nl_log_invio_2019` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_log_invio_2019` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_newsletter`
--

DROP TABLE IF EXISTS `nl_newsletter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_newsletter` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `data_invio` date NOT NULL,
  `oggetto` varchar(255) NOT NULL,
  `testo` longtext NOT NULL,
  `flag_invio` tinyint(1) NOT NULL,
  `dt_ultima_modifica` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=239 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_newsletter`
--

LOCK TABLES `nl_newsletter` WRITE;
/*!40000 ALTER TABLE `nl_newsletter` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_newsletter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_newsletter_lista`
--

DROP TABLE IF EXISTS `nl_newsletter_lista`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_newsletter_lista` (
  `id_newsletter` bigint(20) unsigned NOT NULL,
  `id_lista` int(11) NOT NULL,
  PRIMARY KEY (`id_newsletter`,`id_lista`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_newsletter_lista`
--

LOCK TABLES `nl_newsletter_lista` WRITE;
/*!40000 ALTER TABLE `nl_newsletter_lista` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_newsletter_lista` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nl_template`
--

DROP TABLE IF EXISTS `nl_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nl_template` (
  `id` int(11) NOT NULL,
  `header` longtext NOT NULL,
  `body` longtext NOT NULL,
  `footer` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nl_template`
--

LOCK TABLES `nl_template` WRITE;
/*!40000 ALTER TABLE `nl_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `nl_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progetto`
--

DROP TABLE IF EXISTS `progetto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `progetto` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_progetto_procedura` bigint(20) unsigned NOT NULL,
  `visibilita` int(1) NOT NULL,
  `data_creazione` date NOT NULL,
  `flag_archivio` tinyint(1) NOT NULL,
  `data_archiviazione` date NOT NULL,
  `nome` varchar(255) NOT NULL DEFAULT '',
  `descrizione` longtext NOT NULL,
  `dt_ultima_modifica` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1153 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progetto`
--

LOCK TABLES `progetto` WRITE;
/*!40000 ALTER TABLE `progetto` DISABLE KEYS */;
/*!40000 ALTER TABLE `progetto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progetto_documento`
--

DROP TABLE IF EXISTS `progetto_documento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `progetto_documento` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_progetto` bigint(20) unsigned NOT NULL,
  `visibilita` int(1) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL DEFAULT '',
  `dt_upload` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `utente_upload` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progetto_documento`
--

LOCK TABLES `progetto_documento` WRITE;
/*!40000 ALTER TABLE `progetto_documento` DISABLE KEYS */;
/*!40000 ALTER TABLE `progetto_documento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progetto_nota`
--

DROP TABLE IF EXISTS `progetto_nota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `progetto_nota` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_progetto` bigint(20) unsigned NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `visibilita` int(1) NOT NULL,
  `data_creazione` date NOT NULL,
  `utente_creazione` varchar(50) NOT NULL DEFAULT '',
  `descrizione` longtext NOT NULL,
  `utente_ultima_modifica` varchar(50) NOT NULL DEFAULT '',
  `dt_ultima_modifica` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30186 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progetto_nota`
--

LOCK TABLES `progetto_nota` WRITE;
/*!40000 ALTER TABLE `progetto_nota` DISABLE KEYS */;
/*!40000 ALTER TABLE `progetto_nota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progetto_procedura`
--

DROP TABLE IF EXISTS `progetto_procedura`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `progetto_procedura` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progetto_procedura`
--

LOCK TABLES `progetto_procedura` WRITE;
/*!40000 ALTER TABLE `progetto_procedura` DISABLE KEYS */;
/*!40000 ALTER TABLE `progetto_procedura` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progetto_procedura_step`
--

DROP TABLE IF EXISTS `progetto_procedura_step`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `progetto_procedura_step` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_progetto_procedura` bigint(20) unsigned NOT NULL,
  `descrizione` longtext NOT NULL,
  `data_scadenza` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progetto_procedura_step`
--

LOCK TABLES `progetto_procedura_step` WRITE;
/*!40000 ALTER TABLE `progetto_procedura_step` DISABLE KEYS */;
/*!40000 ALTER TABLE `progetto_procedura_step` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progetto_procedura_step_chiuso`
--

DROP TABLE IF EXISTS `progetto_procedura_step_chiuso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `progetto_procedura_step_chiuso` (
  `id_progetto` bigint(20) unsigned NOT NULL,
  `id_progetto_procedura_step` bigint(20) unsigned NOT NULL,
  `utente` varchar(50) NOT NULL DEFAULT '',
  `dt_ultima_modifica` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_progetto`,`id_progetto_procedura_step`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progetto_procedura_step_chiuso`
--

LOCK TABLES `progetto_procedura_step_chiuso` WRITE;
/*!40000 ALTER TABLE `progetto_procedura_step_chiuso` DISABLE KEYS */;
/*!40000 ALTER TABLE `progetto_procedura_step_chiuso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progetto_procedura_step_non_attivo`
--

DROP TABLE IF EXISTS `progetto_procedura_step_non_attivo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `progetto_procedura_step_non_attivo` (
  `id_progetto` bigint(20) unsigned NOT NULL,
  `id_progetto_procedura_step` bigint(20) unsigned NOT NULL,
  `utente` varchar(50) NOT NULL DEFAULT '',
  `dt_ultima_modifica` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_progetto`,`id_progetto_procedura_step`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progetto_procedura_step_non_attivo`
--

LOCK TABLES `progetto_procedura_step_non_attivo` WRITE;
/*!40000 ALTER TABLE `progetto_procedura_step_non_attivo` DISABLE KEYS */;
/*!40000 ALTER TABLE `progetto_procedura_step_non_attivo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progetto_template`
--

DROP TABLE IF EXISTS `progetto_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `progetto_template` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progetto_template`
--

LOCK TABLES `progetto_template` WRITE;
/*!40000 ALTER TABLE `progetto_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `progetto_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progetto_template_step`
--

DROP TABLE IF EXISTS `progetto_template_step`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `progetto_template_step` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_progetto_template` bigint(20) unsigned NOT NULL,
  `descrizione` longtext NOT NULL,
  `giorni_scadenza` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progetto_template_step`
--

LOCK TABLES `progetto_template_step` WRITE;
/*!40000 ALTER TABLE `progetto_template_step` DISABLE KEYS */;
/*!40000 ALTER TABLE `progetto_template_step` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scadenza`
--

DROP TABLE IF EXISTS `scadenza`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scadenza` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `tipo` varchar(20) NOT NULL DEFAULT '',
  `visibilita` int(1) NOT NULL,
  `id_bilancio` bigint(20) unsigned NOT NULL,
  `id_progetto` bigint(20) unsigned NOT NULL,
  `id_progetto_nota` bigint(20) unsigned NOT NULL,
  `descrizione` longtext NOT NULL,
  `responsabile` varchar(50) NOT NULL DEFAULT '',
  `utente_ultima_modifica` varchar(50) NOT NULL DEFAULT '',
  `dt_ultima_modifica` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17268 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scadenza`
--

LOCK TABLES `scadenza` WRITE;
/*!40000 ALTER TABLE `scadenza` DISABLE KEYS */;
/*!40000 ALTER TABLE `scadenza` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utente`
--

DROP TABLE IF EXISTS `utente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `utente` (
  `nome_utente` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `livello` int(1) NOT NULL,
  `email` varchar(50) NOT NULL,
  `flag_notifiche_email` tinyint(1) NOT NULL,
  `flag_notifiche_globomail` tinyint(1) NOT NULL,
  `auth_token_login` varchar(200) DEFAULT NULL,
  `auth_token_create` date DEFAULT NULL,
  `auth_token_expire` date DEFAULT NULL,
  PRIMARY KEY (`nome_utente`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utente`
--

LOCK TABLES `utente` WRITE;
/*!40000 ALTER TABLE `utente` DISABLE KEYS */;
/*!40000 ALTER TABLE `utente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'globo_kosmosol'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-24 17:52:59
