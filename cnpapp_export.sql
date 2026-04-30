-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: cnpapp_system
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `academic_days`
--

DROP TABLE IF EXISTS `academic_days`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `academic_days` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `academic_year` int(11) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `date_val` date DEFAULT NULL,
  `activity` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `category` varchar(100) DEFAULT 'ทั่วไป',
  `day_type` varchar(50) DEFAULT 'ปกติ',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `date_val` (`date_val`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_categories`
--

DROP TABLE IF EXISTS `point_categories`;
CREATE TABLE `point_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('positive','negative') NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `point_categories` VALUES 
(1,'ความประพฤติดี','positive','รายการชื่นชมเชิดชูเกียรติ'),
(2,'ระเบียบวินัย','negative','รายการตัดคะแนนด้านระเบียบวินัย'),
(3,'มาสาย/การเข้าเรียน','negative','รายการตัดคะแนนด้านการมาเรียน');

--
-- Table structure for table `point_items`
--

DROP TABLE IF EXISTS `point_items`;
CREATE TABLE `point_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `points` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `point_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `point_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `point_items` VALUES 
(1,1,'ช่วยงานโรงเรียน/ครู',10),
(2,1,'เก็บของหายได้แล้วส่งคืน',20),
(3,1,'จิตอาสาพัฒนาชุมชน',15),
(4,2,'แต่งกายผิดระเบียบ',-5),
(5,2,'ทะเลาะวิวาท',-40),
(6,2,'พกพาสิ่งเสพติด/อาวุธ',-50),
(7,3,'มาสายหลังเข้าแถว',-2),
(8,3,'หนีเรียน',-10);

--
-- Table structure for table `students` (Partial data for structure)
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `student_id` varchar(20) NOT NULL,
  `prefix` varchar(20) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `class` varchar(10) DEFAULT NULL,
  `room` varchar(10) DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ปกติ',
  `credit_score` int(11) DEFAULT 100,
  PRIMARY KEY (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `point_transactions`
--

DROP TABLE IF EXISTS `point_transactions`;
CREATE TABLE `point_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` varchar(20) NOT NULL,
  `item_id` int(11) NOT NULL,
  `points_change` int(11) NOT NULL,
  `remark` text DEFAULT NULL,
  `recorded_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `point_transactions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  CONSTRAINT `point_transactions_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `point_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
