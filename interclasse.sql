-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: interclasse_sesi
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `chaves`
--

DROP TABLE IF EXISTS `chaves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chaves` (
  `CHA_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CHA_NOME` varchar(100) NOT NULL,
  `FK_MOD_ID` int(11) NOT NULL,
  PRIMARY KEY (`CHA_ID`),
  KEY `FK_MOD_ID` (`FK_MOD_ID`),
  CONSTRAINT `chaves_ibfk_1` FOREIGN KEY (`FK_MOD_ID`) REFERENCES `modalidades` (`MOD_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chaves`
--

LOCK TABLES `chaves` WRITE;
/*!40000 ALTER TABLE `chaves` DISABLE KEYS */;
INSERT INTO `chaves` VALUES (1,'Chave A - Futebol',1),(2,'Chave Única - Basquete',2),(3,'Chave Única - Vôlei',3);
/*!40000 ALTER TABLE `chaves` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `confrontos`
--

DROP TABLE IF EXISTS `confrontos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `confrontos` (
  `CON_ID` int(11) NOT NULL AUTO_INCREMENT,
  `FK_CHA_ID` int(11) NOT NULL,
  `FK_FAS_ID` int(11) NOT NULL,
  `FK_TIM_1_ID` int(11) DEFAULT NULL,
  `FK_TIM_2_ID` int(11) DEFAULT NULL,
  `FK_CON_ORIGEM_1_ID` int(11) DEFAULT NULL,
  `FK_CON_ORIGEM_2_ID` int(11) DEFAULT NULL,
  `CON_DATA` date DEFAULT NULL,
  `CON_HORA` time DEFAULT NULL,
  PRIMARY KEY (`CON_ID`),
  UNIQUE KEY `UQ_CONFRONTOS_ORIGEM` (`FK_CON_ORIGEM_1_ID`,`FK_CON_ORIGEM_2_ID`),
  KEY `FK_CHA_ID` (`FK_CHA_ID`),
  KEY `FK_FAS_ID` (`FK_FAS_ID`),
  KEY `FK_TIM_1_ID` (`FK_TIM_1_ID`),
  KEY `FK_TIM_2_ID` (`FK_TIM_2_ID`),
  KEY `FK_CONFRONTOS_ORIGEM_2` (`FK_CON_ORIGEM_2_ID`),
  CONSTRAINT `FK_CONFRONTOS_ORIGEM_1` FOREIGN KEY (`FK_CON_ORIGEM_1_ID`) REFERENCES `confrontos` (`CON_ID`) ON DELETE SET NULL,
  CONSTRAINT `FK_CONFRONTOS_ORIGEM_2` FOREIGN KEY (`FK_CON_ORIGEM_2_ID`) REFERENCES `confrontos` (`CON_ID`) ON DELETE SET NULL,
  CONSTRAINT `confrontos_ibfk_1` FOREIGN KEY (`FK_CHA_ID`) REFERENCES `chaves` (`CHA_ID`),
  CONSTRAINT `confrontos_ibfk_2` FOREIGN KEY (`FK_FAS_ID`) REFERENCES `fases` (`FAS_ID`),
  CONSTRAINT `confrontos_ibfk_3` FOREIGN KEY (`FK_TIM_1_ID`) REFERENCES `times` (`TIM_ID`),
  CONSTRAINT `confrontos_ibfk_4` FOREIGN KEY (`FK_TIM_2_ID`) REFERENCES `times` (`TIM_ID`),
  CONSTRAINT `CONSTRAINT_1` CHECK (`FK_TIM_1_ID` <> `FK_TIM_2_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `confrontos`
--

LOCK TABLES `confrontos` WRITE;
/*!40000 ALTER TABLE `confrontos` DISABLE KEYS */;
INSERT INTO `confrontos` VALUES (9,1,2,11,3,NULL,NULL,'2026-09-22','12:00:00'),(18,3,1,7,9,NULL,NULL,'2026-09-22','15:00:00');
/*!40000 ALTER TABLE `confrontos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fases`
--

DROP TABLE IF EXISTS `fases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fases` (
  `FAS_ID` int(11) NOT NULL AUTO_INCREMENT,
  `FAS_NOME` varchar(50) NOT NULL,
  `FAS_ORDEM` int(10) unsigned NOT NULL,
  PRIMARY KEY (`FAS_ID`),
  UNIQUE KEY `FAS_NOME` (`FAS_NOME`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fases`
--

LOCK TABLES `fases` WRITE;
/*!40000 ALTER TABLE `fases` DISABLE KEYS */;
INSERT INTO `fases` VALUES (1,'Primeira fase',1),(2,'Oitavas de final',2),(3,'Quartas de final',3),(4,'Semifinal',4),(5,'Final',5);
/*!40000 ALTER TABLE `fases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modalidades`
--

DROP TABLE IF EXISTS `modalidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modalidades` (
  `MOD_ID` int(11) NOT NULL AUTO_INCREMENT,
  `MOD_NOME` varchar(100) NOT NULL,
  PRIMARY KEY (`MOD_ID`),
  UNIQUE KEY `MOD_NOME` (`MOD_NOME`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modalidades`
--

LOCK TABLES `modalidades` WRITE;
/*!40000 ALTER TABLE `modalidades` DISABLE KEYS */;
INSERT INTO `modalidades` VALUES (2,'Basquete'),(1,'Futsal'),(3,'Vôlei'),(5,'volei 2');
/*!40000 ALTER TABLE `modalidades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resultados`
--

DROP TABLE IF EXISTS `resultados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resultados` (
  `RES_ID` int(11) NOT NULL AUTO_INCREMENT,
  `FK_CON_ID` int(11) NOT NULL,
  `RES_PONTUACAO_TIME_1` int(11) NOT NULL,
  `RES_PONTUACAO_TIME_2` int(11) NOT NULL,
  `FK_TIM_VENCEDOR_ID` int(11) NOT NULL,
  PRIMARY KEY (`RES_ID`),
  UNIQUE KEY `FK_CON_ID` (`FK_CON_ID`),
  KEY `FK_TIM_VENCEDOR_ID` (`FK_TIM_VENCEDOR_ID`),
  CONSTRAINT `resultados_ibfk_1` FOREIGN KEY (`FK_CON_ID`) REFERENCES `confrontos` (`CON_ID`),
  CONSTRAINT `resultados_ibfk_2` FOREIGN KEY (`FK_TIM_VENCEDOR_ID`) REFERENCES `times` (`TIM_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resultados`
--

LOCK TABLES `resultados` WRITE;
/*!40000 ALTER TABLE `resultados` DISABLE KEYS */;
INSERT INTO `resultados` VALUES (6,9,10,0,11),(11,18,0,8,9);
/*!40000 ALTER TABLE `resultados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `times`
--

DROP TABLE IF EXISTS `times`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `times` (
  `TIM_ID` int(11) NOT NULL AUTO_INCREMENT,
  `FK_TUR_ID` int(11) NOT NULL,
  `FK_MOD_ID` int(11) NOT NULL,
  PRIMARY KEY (`TIM_ID`),
  UNIQUE KEY `FK_TUR_ID` (`FK_TUR_ID`,`FK_MOD_ID`),
  KEY `FK_MOD_ID` (`FK_MOD_ID`),
  CONSTRAINT `times_ibfk_1` FOREIGN KEY (`FK_TUR_ID`) REFERENCES `turmas` (`TUR_ID`),
  CONSTRAINT `times_ibfk_2` FOREIGN KEY (`FK_MOD_ID`) REFERENCES `modalidades` (`MOD_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `times`
--

LOCK TABLES `times` WRITE;
/*!40000 ALTER TABLE `times` DISABLE KEYS */;
INSERT INTO `times` VALUES (1,1,1),(5,1,2),(2,2,1),(7,2,3),(3,3,1),(6,3,2),(11,5,1),(10,5,2),(9,5,3);
/*!40000 ALTER TABLE `times` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `turmas`
--

DROP TABLE IF EXISTS `turmas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `turmas` (
  `TUR_ID` int(11) NOT NULL AUTO_INCREMENT,
  `TUR_SERIE` varchar(30) NOT NULL,
  PRIMARY KEY (`TUR_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `turmas`
--

LOCK TABLES `turmas` WRITE;
/*!40000 ALTER TABLE `turmas` DISABLE KEYS */;
INSERT INTO `turmas` VALUES (1,'1° EM'),(2,'9° FUND'),(3,'2° EM'),(5,'3° EM');
/*!40000 ALTER TABLE `turmas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `USU_ID` int(11) NOT NULL AUTO_INCREMENT,
  `USU_NOME` varchar(100) NOT NULL,
  `USU_EMAIL` varchar(150) NOT NULL,
  `USU_SENHA` varchar(255) NOT NULL,
  PRIMARY KEY (`USU_ID`),
  UNIQUE KEY `USU_EMAIL` (`USU_EMAIL`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'admin','admin@interclassesesi.com.br','$2y$10$9.ivONl1eJ6xkoUlK02zYO05TKAhmcH94zCkH0BH83OzwwPMoQ4jy');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-22 11:44:08
