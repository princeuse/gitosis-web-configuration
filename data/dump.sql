-- MySQL dump 10.13  Distrib 5.1.54, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: gitosis
-- ------------------------------------------------------
-- Server version	5.1.54-1ubuntu4

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
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_users` (
  `admin_id` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `admin_login` varchar(200) NOT NULL,
  `admin_password` varchar(200) NOT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gitosis_group_rights`
--

DROP TABLE IF EXISTS `gitosis_group_rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gitosis_group_rights` (
  `gitosis_group_right_id` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `gitosis_group_id` int(15) unsigned NOT NULL,
  `gitosis_repository_id` int(15) unsigned NOT NULL,
  `is_writeable` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`gitosis_group_right_id`),
  KEY `GROUP_RIGHTS` (`gitosis_group_id`),
  KEY `REPO_RIGHTS` (`gitosis_repository_id`),
  CONSTRAINT `GROUP_RIGHTS` FOREIGN KEY (`gitosis_group_id`) REFERENCES `gitosis_groups` (`gitosis_group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `REPO_RIGHTS` FOREIGN KEY (`gitosis_repository_id`) REFERENCES `gitosis_repositories` (`gitosis_repository_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gitosis_groups`
--

DROP TABLE IF EXISTS `gitosis_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gitosis_groups` (
  `gitosis_group_id` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `gitosis_group_name` varchar(200) NOT NULL,
  PRIMARY KEY (`gitosis_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gitosis_repositories`
--

DROP TABLE IF EXISTS `gitosis_repositories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gitosis_repositories` (
  `gitosis_repository_id` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `gitosis_repository_owner_id` int(15) unsigned DEFAULT NULL,
  `gitosis_repository_name` varchar(200) NOT NULL,
  `gitosis_repository_description` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`gitosis_repository_id`),
  KEY `REPO_OWNER` (`gitosis_repository_owner_id`),
  CONSTRAINT `REPO_OWNER` FOREIGN KEY (`gitosis_repository_owner_id`) REFERENCES `gitosis_users` (`gitosis_user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gitosis_user_groups`
--

DROP TABLE IF EXISTS `gitosis_user_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gitosis_user_groups` (
  `gitosis_user_group_id` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `gitosis_group_id` int(15) unsigned NOT NULL,
  `gitosis_user_id` int(15) unsigned NOT NULL,
  PRIMARY KEY (`gitosis_user_group_id`),
  KEY `GROUP_USER` (`gitosis_group_id`),
  KEY `USER_GROUP` (`gitosis_user_id`),
  CONSTRAINT `GROUP_USER` FOREIGN KEY (`gitosis_group_id`) REFERENCES `gitosis_groups` (`gitosis_group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `USER_GROUP` FOREIGN KEY (`gitosis_user_id`) REFERENCES `gitosis_users` (`gitosis_user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gitosis_users`
--

DROP TABLE IF EXISTS `gitosis_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gitosis_users` (
  `gitosis_user_id` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `gitosis_user_name` varchar(200) NOT NULL,
  `gitosis_user_email` varchar(200) NOT NULL,
  `gitosis_user_ssh_key` text NOT NULL,
  PRIMARY KEY (`gitosis_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gitosis_web_configuration`
--

DROP TABLE IF EXISTS `gitosis_web_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gitosis_web_configuration` (
  `config_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `config_name` varchar(200) COLLATE utf8_bin NOT NULL,
  `config_value` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-05-08 14:04:02
