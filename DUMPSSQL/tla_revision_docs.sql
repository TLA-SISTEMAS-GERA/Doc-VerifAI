-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: tla_revision_docs
-- ------------------------------------------------------
-- Server version	11.8.5-MariaDB

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
-- Table structure for table `td_documento_detalle`
--

DROP TABLE IF EXISTS `td_documento_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `td_documento_detalle` (
  `docd_id` int(11) NOT NULL AUTO_INCREMENT,
  `det_id` int(11) NOT NULL,
  `doc_nom` varchar(250) NOT NULL,
  `est` int(11) NOT NULL,
  PRIMARY KEY (`docd_id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `td_documento_detalle`
--

--
-- Table structure for table `tm_consulta`
--

DROP TABLE IF EXISTS `tm_consulta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tm_consulta` (
  `cons_id` int(11) NOT NULL AUTO_INCREMENT,
  `usu_id` int(11) DEFAULT NULL,
  `cons_nom` varchar(250) DEFAULT NULL,
  `nom_bucket` varchar(250) DEFAULT NULL COMMENT 'Nombre consulta = nombre bucket',
  `fech_crea` datetime DEFAULT NULL,
  PRIMARY KEY (`cons_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabla de consultas';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tm_consulta`
--

--
-- Table structure for table `tm_detalle`
--

DROP TABLE IF EXISTS `tm_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tm_detalle` (
  `det_id` int(11) NOT NULL AUTO_INCREMENT,
  `cons_id` int(11) NOT NULL,
  `usu_id` int(11) NOT NULL,
  `det_contenido` mediumtext NOT NULL,
  `fech_crea` datetime NOT NULL,
  PRIMARY KEY (`det_id`)
) ENGINE=InnoDB AUTO_INCREMENT=337 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tm_detalle`
--

--
-- Table structure for table `tm_usuario`
--

DROP TABLE IF EXISTS `tm_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tm_usuario` (
  `usu_id` int(11) NOT NULL AUTO_INCREMENT,
  `usu_nom` varchar(150) DEFAULT NULL,
  `usu_ape` varchar(150) DEFAULT NULL,
  `usu_correo` varchar(150) NOT NULL,
  `usu_pass` varchar(29) NOT NULL,
  `fech_crea` datetime DEFAULT NULL,
  `fech_modi` datetime DEFAULT NULL,
  `fech_elim` datetime DEFAULT NULL,
  `est` int(11) NOT NULL,
  PRIMARY KEY (`usu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabla de Usuarios';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tm_usuario`
--

LOCK TABLES `tm_usuario` WRITE;
/*!40000 ALTER TABLE `tm_usuario` DISABLE KEYS */;
INSERT INTO `tm_usuario` VALUES (1,'Usuario ','Prueba','example@gmail.com','123456','2025-11-18 13:57:58',NULL,NULL,1),(2,'Gemini','AI','','','2025-12-03 10:04:19',NULL,NULL,1);
/*!40000 ALTER TABLE `tm_usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-13 11:27:06
