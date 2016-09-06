-- MySQL dump 10.13  Distrib 5.5.49, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: virtcms
-- ------------------------------------------------------
-- Server version	5.5.49-0+deb8u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `vms_user`
--

DROP TABLE IF EXISTS `vms_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vms_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `passwd` varchar(100) NOT NULL,
  `logintime` varchar(100) DEFAULT NULL,
  `seed` char(10) NOT NULL,
  `status` tinyint(2) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vms_user`
--

LOCK TABLES `vms_user` WRITE;
/*!40000 ALTER TABLE `vms_user` DISABLE KEYS */;
INSERT INTO `vms_user` VALUES (1,'root','82790085228cf8a1e3bac41f45271e5f',NULL,'123456',0);
/*!40000 ALTER TABLE `vms_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vms_vm`
--

DROP TABLE IF EXISTS `vms_vm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vms_vm` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `ip` varchar(100) NOT NULL DEFAULT '0.0.0.0',
  `guest_counts` int(10) DEFAULT '0',
  `max_counts` int(10) DEFAULT '50',
  `createtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` tinyint(2) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vms_vm`
--

LOCK TABLES `vms_vm` WRITE;
/*!40000 ALTER TABLE `vms_vm` DISABLE KEYS */;
/*!40000 ALTER TABLE `vms_vm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vms_vm_image`
--

DROP TABLE IF EXISTS `vms_vm_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vms_vm_image` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `ip` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vms_vm_image`
--

LOCK TABLES `vms_vm_image` WRITE;
/*!40000 ALTER TABLE `vms_vm_image` DISABLE KEYS */;
/*!40000 ALTER TABLE `vms_vm_image` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vms_vm_log`
--

DROP TABLE IF EXISTS `vms_vm_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vms_vm_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `img_path` varchar(100) DEFAULT NULL,
  `type` varchar(100) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `createtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` tinyint(2) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vms_vm_log`
--

LOCK TABLES `vms_vm_log` WRITE;
/*!40000 ALTER TABLE `vms_vm_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `vms_vm_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vms_vm_tpl`
--

DROP TABLE IF EXISTS `vms_vm_tpl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vms_vm_tpl` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `tpl` text NOT NULL,
  `status` tinyint(2) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vms_vm_tpl`
--

LOCK TABLES `vms_vm_tpl` WRITE;
/*!40000 ALTER TABLE `vms_vm_tpl` DISABLE KEYS */;
/*!40000 ALTER TABLE `vms_vm_tpl` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-09-06 15:36:16
