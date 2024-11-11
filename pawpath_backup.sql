-- MySQL dump 10.13  Distrib 8.0.39, for Linux (x86_64)
--
-- Host: localhost    Database: pawpath
-- ------------------------------------------------------
-- Server version	8.0.39-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Adoption_Application`
--

DROP TABLE IF EXISTS `Adoption_Application`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Adoption_Application` (
  `application_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `pet_id` int NOT NULL,
  `application_date` date NOT NULL,
  `status` varchar(20) NOT NULL,
  `status_history` json DEFAULT NULL,
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `reviewed_by` int DEFAULT NULL,
  PRIMARY KEY (`application_id`),
  KEY `user_id` (`user_id`),
  KEY `pet_id` (`pet_id`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `Adoption_Application_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`),
  CONSTRAINT `Adoption_Application_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `Pet` (`pet_id`),
  CONSTRAINT `Adoption_Application_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `User` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Adoption_Application`
--

LOCK TABLES `Adoption_Application` WRITE;
/*!40000 ALTER TABLE `Adoption_Application` DISABLE KEYS */;
INSERT INTO `Adoption_Application` VALUES (1,1,2,'2024-11-06','under_review',NULL,'2024-11-11 18:41:19',NULL);
/*!40000 ALTER TABLE `Adoption_Application` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Application_Document`
--

DROP TABLE IF EXISTS `Application_Document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Application_Document` (
  `document_id` int NOT NULL AUTO_INCREMENT,
  `application_id` int NOT NULL,
  `document_type` enum('id','proof_of_residence','reference','other') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `verified` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`document_id`),
  KEY `idx_application_docs` (`application_id`),
  CONSTRAINT `Application_Document_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `Adoption_Application` (`application_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Application_Document`
--

LOCK TABLES `Application_Document` WRITE;
/*!40000 ALTER TABLE `Application_Document` DISABLE KEYS */;
/*!40000 ALTER TABLE `Application_Document` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Application_Response`
--

DROP TABLE IF EXISTS `Application_Response`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Application_Response` (
  `response_id` int NOT NULL AUTO_INCREMENT,
  `application_id` int NOT NULL,
  `question_key` varchar(50) NOT NULL,
  `response` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`response_id`),
  KEY `idx_application_responses` (`application_id`),
  CONSTRAINT `Application_Response_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `Adoption_Application` (`application_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Application_Response`
--

LOCK TABLES `Application_Response` WRITE;
/*!40000 ALTER TABLE `Application_Response` DISABLE KEYS */;
/*!40000 ALTER TABLE `Application_Response` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Blog_Post`
--

DROP TABLE IF EXISTS `Blog_Post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Blog_Post` (
  `post_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `publication_date` date NOT NULL,
  `author_id` int NOT NULL,
  PRIMARY KEY (`post_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `Blog_Post_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `User` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Blog_Post`
--

LOCK TABLES `Blog_Post` WRITE;
/*!40000 ALTER TABLE `Blog_Post` DISABLE KEYS */;
INSERT INTO `Blog_Post` VALUES (1,'Updated Test Blog Post_1731118775','This is updated content for timestamp 1731118775','2024-11-08',1),(2,'Product Review_1731118775','This is a review of Test Product_1731118775','2024-11-08',1),(3,'Updated Test Blog Post_1731119307','This is updated content for timestamp 1731119307','2024-11-08',1),(4,'Product Review_1731119307','This is a review of Test Product_1731119307','2024-11-08',1),(5,'Updated Test Blog Post_1731120779','This is updated content for timestamp 1731120779','2024-11-08',1),(6,'Product Review_1731120779','This is a review of Test Product_1731120779','2024-11-08',1),(7,'Updated Test Blog Post_1731121127','This is updated content for timestamp 1731121127','2024-11-08',1),(8,'Product Review_1731121127','This is a review of Test Product_1731121127','2024-11-08',1),(9,'Updated Test Blog Post_1731121553','This is updated content for timestamp 1731121553','2024-11-08',1),(10,'Product Review_1731121553','This is a review of Test Product_1731121553','2024-11-08',1),(11,'Updated Test Blog Post_1731121771','This is updated content for timestamp 1731121771','2024-11-08',1),(12,'Product Review_1731121771','This is a review of Test Product_1731121771','2024-11-08',1),(13,'Updated Test Blog Post_1731121999','This is updated content for timestamp 1731121999','2024-11-08',1),(14,'Product Review_1731121999','This is a review of Test Product_1731121999','2024-11-08',1),(15,'Updated Test Blog Post_1731122202','This is updated content for timestamp 1731122202','2024-11-08',1),(16,'Product Review_1731122202','This is a review of Test Product_1731122202','2024-11-08',1),(17,'Updated Test Blog Post_1731122309','This is updated content for timestamp 1731122309','2024-11-08',1),(18,'Product Review_1731122309','This is a review of Test Product_1731122309','2024-11-08',1),(19,'Updated Test Blog Post_1731122381','This is updated content for timestamp 1731122381','2024-11-08',1),(20,'Product Review_1731122381','This is a review of Test Product_1731122381','2024-11-08',1),(21,'Updated Test Blog Post_1731122627','This is updated content for timestamp 1731122627','2024-11-08',1),(22,'Product Review_1731122627','This is a review of Test Product_1731122627','2024-11-08',1),(23,'Updated Test Blog Post_1731122846','This is updated content for timestamp 1731122846','2024-11-08',1),(24,'Product Review_1731122846','This is a review of Test Product_1731122846','2024-11-08',1),(25,'Updated Test Blog Post_1731123080','This is updated content for timestamp 1731123080','2024-11-08',1),(26,'Product Review_1731123080','This is a review of Test Product_1731123080','2024-11-08',1),(27,'Updated Test Blog Post_1731123154','This is updated content for timestamp 1731123154','2024-11-08',1),(28,'Product Review_1731123154','This is a review of Test Product_1731123154','2024-11-08',1),(29,'Updated Test Blog Post_1731123275','This is updated content for timestamp 1731123275','2024-11-08',1),(30,'Product Review_1731123275','This is a review of Test Product_1731123275','2024-11-08',1),(31,'Updated Test Blog Post_1731123385','This is updated content for timestamp 1731123385','2024-11-08',1),(32,'Product Review_1731123385','This is a review of Test Product_1731123385','2024-11-08',1),(33,'Updated Test Blog Post_1731125861','This is updated content for timestamp 1731125861','2024-11-08',1),(34,'Product Review_1731125861','This is a review of Test Product_1731125861','2024-11-08',1),(35,'Updated Test Blog Post_1731125904','This is updated content for timestamp 1731125904','2024-11-08',1),(36,'Product Review_1731125904','This is a review of Test Product_1731125904','2024-11-08',1),(37,'Updated Test Blog Post_1731126433','This is updated content for timestamp 1731126433','2024-11-08',1),(38,'Product Review_1731126433','This is a review of Test Product_1731126433','2024-11-08',1),(39,'Updated Test Blog Post_1731126548','This is updated content for timestamp 1731126548','2024-11-08',1),(40,'Product Review_1731126548','This is a review of Test Product_1731126548','2024-11-08',1),(41,'Updated Test Blog Post_1731126776','This is updated content for timestamp 1731126776','2024-11-08',1),(42,'Product Review_1731126776','This is a review of Test Product_1731126776','2024-11-08',1),(43,'Updated Test Blog Post_1731126846','This is updated content for timestamp 1731126846','2024-11-08',1),(44,'Product Review_1731126846','This is a review of Test Product_1731126846','2024-11-08',1),(45,'Updated Test Blog Post_1731126970','This is updated content for timestamp 1731126970','2024-11-08',1),(46,'Product Review_1731126970','This is a review of Test Product_1731126970','2024-11-08',1),(47,'Updated Test Blog Post_1731127148','This is updated content for timestamp 1731127148','2024-11-08',1),(48,'Product Review_1731127148','This is a review of Test Product_1731127148','2024-11-08',1),(49,'Updated Test Blog Post_1731127282','This is updated content for timestamp 1731127282','2024-11-08',1),(50,'Product Review_1731127282','This is a review of Test Product_1731127282','2024-11-08',1),(51,'Updated Test Blog Post_1731127322','This is updated content for timestamp 1731127322','2024-11-08',1),(52,'Product Review_1731127322','This is a review of Test Product_1731127322','2024-11-08',1),(53,'Updated Test Blog Post_1731127455','This is updated content for timestamp 1731127455','2024-11-08',1),(54,'Product Review_1731127455','This is a review of Test Product_1731127455','2024-11-08',1),(55,'Updated Test Blog Post_1731127719','This is updated content for timestamp 1731127719','2024-11-08',1),(56,'Product Review_1731127719','This is a review of Test Product_1731127719','2024-11-08',1),(57,'Updated Test Blog Post_1731127835','This is updated content for timestamp 1731127835','2024-11-08',1),(58,'Product Review_1731127835','This is a review of Test Product_1731127835','2024-11-08',1),(59,'Updated Test Blog Post_1731171875','This is updated content for timestamp 1731171875','2024-11-09',1),(60,'Product Review_1731171875','This is a review of Test Product_1731171875','2024-11-09',1),(61,'Updated Test Blog Post_1731172697','This is updated content for timestamp 1731172697','2024-11-09',1),(62,'Product Review_1731172697','This is a review of Test Product_1731172697','2024-11-09',1),(63,'Updated Test Blog Post_1731172835','This is updated content for timestamp 1731172835','2024-11-09',1),(64,'Product Review_1731172835','This is a review of Test Product_1731172835','2024-11-09',1),(65,'Updated Test Blog Post_1731172885','This is updated content for timestamp 1731172885','2024-11-09',1),(66,'Product Review_1731172885','This is a review of Test Product_1731172885','2024-11-09',1),(67,'Updated Test Blog Post_1731173000','This is updated content for timestamp 1731173000','2024-11-09',1),(68,'Product Review_1731173000','This is a review of Test Product_1731173000','2024-11-09',1),(69,'Updated Test Blog Post_1731173528','This is updated content for timestamp 1731173528','2024-11-09',1),(70,'Product Review_1731173528','This is a review of Test Product_1731173528','2024-11-09',1),(71,'Updated Test Blog Post_1731173610','This is updated content for timestamp 1731173610','2024-11-09',1),(72,'Product Review_1731173610','This is a review of Test Product_1731173610','2024-11-09',1),(73,'Updated Test Blog Post_1731173708','This is updated content for timestamp 1731173708','2024-11-09',1),(74,'Product Review_1731173708','This is a review of Test Product_1731173708','2024-11-09',1),(75,'Updated Test Blog Post_1731173875','This is updated content for timestamp 1731173875','2024-11-09',1),(76,'Product Review_1731173875','This is a review of Test Product_1731173875','2024-11-09',1),(77,'Updated Test Blog Post_1731174320','This is updated content for timestamp 1731174320','2024-11-09',1),(78,'Product Review_1731174320','This is a review of Test Product_1731174320','2024-11-09',1),(79,'Updated Test Blog Post_1731174444','This is updated content for timestamp 1731174444','2024-11-09',1),(80,'Product Review_1731174444','This is a review of Test Product_1731174444','2024-11-09',1),(81,'Updated Test Blog Post_1731174523','This is updated content for timestamp 1731174523','2024-11-09',1),(82,'Product Review_1731174523','This is a review of Test Product_1731174523','2024-11-09',1),(83,'Updated Test Blog Post_1731174578','This is updated content for timestamp 1731174578','2024-11-09',1),(84,'Product Review_1731174578','This is a review of Test Product_1731174578','2024-11-09',1),(85,'Updated Test Blog Post_1731175117','This is updated content for timestamp 1731175117','2024-11-09',1),(86,'Product Review_1731175117','This is a review of Test Product_1731175117','2024-11-09',1),(87,'Updated Test Blog Post_1731175247','This is updated content for timestamp 1731175247','2024-11-09',1),(88,'Product Review_1731175247','This is a review of Test Product_1731175247','2024-11-09',1),(89,'Updated Test Blog Post_1731175685','This is updated content for timestamp 1731175685','2024-11-09',1),(90,'Product Review_1731175685','This is a review of Test Product_1731175685','2024-11-09',1),(91,'Updated Test Blog Post_1731177516','This is updated content for timestamp 1731177516','2024-11-09',1),(92,'Product Review_1731177516','This is a review of Test Product_1731177516','2024-11-09',1),(93,'Updated Test Blog Post_1731178110','This is updated content for timestamp 1731178110','2024-11-09',1),(94,'Product Review_1731178110','This is a review of Test Product_1731178110','2024-11-09',1),(95,'Updated Test Blog Post_1731178234','This is updated content for timestamp 1731178234','2024-11-09',1),(96,'Product Review_1731178234','This is a review of Test Product_1731178234','2024-11-09',1),(97,'Updated Test Blog Post_1731179008','This is updated content for timestamp 1731179008','2024-11-09',1),(98,'Product Review_1731179008','This is a review of Test Product_1731179008','2024-11-09',1);
/*!40000 ALTER TABLE `Blog_Post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Blog_Product_Relation`
--

DROP TABLE IF EXISTS `Blog_Product_Relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Blog_Product_Relation` (
  `post_id` int NOT NULL,
  `product_id` int NOT NULL,
  PRIMARY KEY (`post_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `Blog_Product_Relation_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `Blog_Post` (`post_id`),
  CONSTRAINT `Blog_Product_Relation_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `Product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Blog_Product_Relation`
--

LOCK TABLES `Blog_Product_Relation` WRITE;
/*!40000 ALTER TABLE `Blog_Product_Relation` DISABLE KEYS */;
INSERT INTO `Blog_Product_Relation` VALUES (24,1),(26,2),(28,3),(30,4),(32,5),(34,6),(36,7),(38,8),(40,9),(42,10),(44,11),(46,12),(48,13),(50,14),(52,15),(54,16),(56,17),(58,18),(60,19),(62,20),(64,21),(66,22),(68,23),(70,24),(72,25),(74,26),(76,27),(78,28),(80,29),(82,30),(84,31),(86,32),(88,33),(90,34),(92,35),(94,36),(96,37),(98,38);
/*!40000 ALTER TABLE `Blog_Product_Relation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PasswordReset`
--

DROP TABLE IF EXISTS `PasswordReset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PasswordReset` (
  `reset_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reset_id`),
  KEY `idx_token` (`token`),
  KEY `idx_user_reset` (`user_id`,`used`),
  CONSTRAINT `PasswordReset_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PasswordReset`
--

LOCK TABLES `PasswordReset` WRITE;
/*!40000 ALTER TABLE `PasswordReset` DISABLE KEYS */;
/*!40000 ALTER TABLE `PasswordReset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Pet`
--

DROP TABLE IF EXISTS `Pet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Pet` (
  `pet_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `species` varchar(50) NOT NULL,
  `breed` varchar(50) DEFAULT NULL,
  `age` int DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `description` text,
  `shelter_id` int NOT NULL,
  PRIMARY KEY (`pet_id`),
  KEY `shelter_id` (`shelter_id`),
  CONSTRAINT `Pet_ibfk_1` FOREIGN KEY (`shelter_id`) REFERENCES `Shelter` (`shelter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Pet`
--

LOCK TABLES `Pet` WRITE;
/*!40000 ALTER TABLE `Pet` DISABLE KEYS */;
INSERT INTO `Pet` VALUES (2,'Max','Dog','Golden Retriever',4,'Male','A very good and friendly boy',3),(4,'Luna','Cat','Siamese',2,'Female','A gentle and loving cat',3),(69,'Luna','dog','Golden Retriever',2,'female','Energetic and friendly Golden',38),(70,'Max','dog','German Shepherd',3,'male','Intelligent and active shepherd',38),(71,'Max_1731102316','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',54),(72,'Max_1731102403','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',56),(73,'Max_1731102536','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',58),(74,'Max_1731102858','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',60),(75,'Max_1731103070','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',62),(76,'Max_1731103255','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',64),(77,'Max_1731103412','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',66),(78,'Max_1731118512','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',68),(79,'Max_1731118775','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',70),(80,'Max_1731119307','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',72),(81,'Max_1731120779','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',74),(82,'Max_1731121127','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',76),(83,'Max_1731121553','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',78),(84,'Max_1731121771','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',80),(85,'Max_1731121999','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',82),(86,'Max_1731122202','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',84),(87,'Max_1731122309','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',86),(88,'Max_1731122381','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',88),(89,'Max_1731122627','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',90),(90,'Max_1731122846','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',92),(91,'Max_1731123080','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',94),(92,'Max_1731123154','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',96),(93,'Max_1731123275','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',98),(94,'Max_1731123385','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',100),(95,'Max_1731174578','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',175),(96,'Photo_Pet_1731174578','Dog','Mixed',2,'Male','Test pet for image upload',176),(97,'Max_1731175117','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',178),(98,'Photo_Pet_1731175117','Dog','Mixed',2,'Male','Test pet for image upload',179),(99,'Max_1731175247','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',181),(100,'Photo_Pet_1731175247','Dog','Mixed',2,'Male','Test pet for image upload',182),(101,'Max_1731175685','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',184),(102,'Photo_Pet_1731175685','Dog','Mixed',2,'Male','Test pet for image upload',185),(103,'Max_1731177516','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',187),(104,'Max_1731178110','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',189),(105,'Max_1731178234','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',191),(106,'Max_1731178398','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',193),(112,'Max_1731179008','Dog','Golden Retriever',2,'Male','A friendly dog looking for a home',201);
/*!40000 ALTER TABLE `Pet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Pet_Images`
--

DROP TABLE IF EXISTS `Pet_Images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Pet_Images` (
  `image_id` int NOT NULL AUTO_INCREMENT,
  `pet_id` int NOT NULL,
  `original_path` varchar(255) NOT NULL,
  `thumbnail_path` varchar(255) NOT NULL,
  `medium_path` varchar(255) NOT NULL,
  `large_path` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT '0',
  `upload_date` datetime NOT NULL,
  PRIMARY KEY (`image_id`),
  KEY `idx_pet_images` (`pet_id`,`is_main`),
  CONSTRAINT `Pet_Images_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `Pet` (`pet_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Pet_Images`
--

LOCK TABLES `Pet_Images` WRITE;
/*!40000 ALTER TABLE `Pet_Images` DISABLE KEYS */;
/*!40000 ALTER TABLE `Pet_Images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Pet_Trait`
--

DROP TABLE IF EXISTS `Pet_Trait`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Pet_Trait` (
  `trait_id` int NOT NULL AUTO_INCREMENT,
  `trait_name` varchar(50) NOT NULL,
  `category_id` int DEFAULT NULL,
  `value_type` enum('binary','scale','enum') DEFAULT 'binary',
  `possible_values` json DEFAULT NULL,
  PRIMARY KEY (`trait_id`),
  UNIQUE KEY `trait_name` (`trait_name`),
  KEY `idx_pet_trait_category` (`category_id`),
  CONSTRAINT `Pet_Trait_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `Trait_Category` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Pet_Trait`
--

LOCK TABLES `Pet_Trait` WRITE;
/*!40000 ALTER TABLE `Pet_Trait` DISABLE KEYS */;
INSERT INTO `Pet_Trait` VALUES (1,'Friendly',NULL,'binary',NULL),(2,'Gentle',NULL,'binary',NULL),(3,'Playful',NULL,'binary',NULL),(4,'Quiet',NULL,'binary',NULL),(5,'Good with kids',6,'scale','[\"low\", \"medium\", \"high\"]'),(20,'High Energy',1,'binary',NULL),(21,'Calm',1,'binary',NULL),(22,'Independent',2,'binary',NULL),(23,'Needs Company',2,'binary',NULL),(24,'Social',3,'scale','[\"low\", \"medium\", \"high\"]'),(25,'Easy to Groom',4,'binary',NULL),(26,'High Maintenance',4,'binary',NULL),(27,'Easily Trained',5,'binary',NULL),(28,'Good with Pets',7,'binary',NULL),(29,'Vocal',8,'binary',NULL),(30,'Apartment Friendly',9,'binary',NULL),(31,'Special Care Required',10,'binary',NULL);
/*!40000 ALTER TABLE `Pet_Trait` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Pet_Trait_Relation`
--

DROP TABLE IF EXISTS `Pet_Trait_Relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Pet_Trait_Relation` (
  `pet_id` int NOT NULL,
  `trait_id` int NOT NULL,
  PRIMARY KEY (`pet_id`,`trait_id`),
  KEY `idx_trait_relation_trait` (`trait_id`),
  KEY `idx_trait_relation_pet` (`pet_id`),
  CONSTRAINT `Pet_Trait_Relation_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `Pet` (`pet_id`),
  CONSTRAINT `Pet_Trait_Relation_ibfk_2` FOREIGN KEY (`trait_id`) REFERENCES `Pet_Trait` (`trait_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Pet_Trait_Relation`
--

LOCK TABLES `Pet_Trait_Relation` WRITE;
/*!40000 ALTER TABLE `Pet_Trait_Relation` DISABLE KEYS */;
INSERT INTO `Pet_Trait_Relation` VALUES (2,1),(4,1),(2,2),(4,2),(4,3),(69,5),(69,20),(70,20),(69,27),(70,27);
/*!40000 ALTER TABLE `Pet_Trait_Relation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Product`
--

DROP TABLE IF EXISTS `Product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Product` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `affiliate_link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Product`
--

LOCK TABLES `Product` WRITE;
/*!40000 ALTER TABLE `Product` DISABLE KEYS */;
INSERT INTO `Product` VALUES (1,'Test Product_1731122846','Updated product description for timestamp 1731122846',34.99,'https://example.com/product_1731122846'),(2,'Test Product_1731123080','Updated product description for timestamp 1731123080',34.99,'https://example.com/product_1731123080'),(3,'Test Product_1731123154','Updated product description for timestamp 1731123154',34.99,'https://example.com/product_1731123154'),(4,'Test Product_1731123275','Updated product description for timestamp 1731123275',34.99,'https://example.com/product_1731123275'),(5,'Test Product_1731123385','Updated product description for timestamp 1731123385',34.99,'https://example.com/product_1731123385'),(6,'Test Product_1731125861','Updated product description for timestamp 1731125861',34.99,'https://example.com/product_1731125861'),(7,'Test Product_1731125904','Updated product description for timestamp 1731125904',34.99,'https://example.com/product_1731125904'),(8,'Test Product_1731126433','Updated product description for timestamp 1731126433',34.99,'https://example.com/product_1731126433'),(9,'Test Product_1731126548','Updated product description for timestamp 1731126548',34.99,'https://example.com/product_1731126548'),(10,'Test Product_1731126776','Updated product description for timestamp 1731126776',34.99,'https://example.com/product_1731126776'),(11,'Test Product_1731126846','Updated product description for timestamp 1731126846',34.99,'https://example.com/product_1731126846'),(12,'Test Product_1731126970','Updated product description for timestamp 1731126970',34.99,'https://example.com/product_1731126970'),(13,'Test Product_1731127148','Updated product description for timestamp 1731127148',34.99,'https://example.com/product_1731127148'),(14,'Test Product_1731127282','Updated product description for timestamp 1731127282',34.99,'https://example.com/product_1731127282'),(15,'Test Product_1731127322','Updated product description for timestamp 1731127322',34.99,'https://example.com/product_1731127322'),(16,'Test Product_1731127455','Updated product description for timestamp 1731127455',34.99,'https://example.com/product_1731127455'),(17,'Test Product_1731127719','Updated product description for timestamp 1731127719',34.99,'https://example.com/product_1731127719'),(18,'Test Product_1731127835','Updated product description for timestamp 1731127835',34.99,'https://example.com/product_1731127835'),(19,'Test Product_1731171875','Updated product description for timestamp 1731171875',34.99,'https://example.com/product_1731171875'),(20,'Test Product_1731172697','Updated product description for timestamp 1731172697',34.99,'https://example.com/product_1731172697'),(21,'Test Product_1731172835','Updated product description for timestamp 1731172835',34.99,'https://example.com/product_1731172835'),(22,'Test Product_1731172885','Updated product description for timestamp 1731172885',34.99,'https://example.com/product_1731172885'),(23,'Test Product_1731173000','Updated product description for timestamp 1731173000',34.99,'https://example.com/product_1731173000'),(24,'Test Product_1731173528','Updated product description for timestamp 1731173528',34.99,'https://example.com/product_1731173528'),(25,'Test Product_1731173610','Updated product description for timestamp 1731173610',34.99,'https://example.com/product_1731173610'),(26,'Test Product_1731173708','Updated product description for timestamp 1731173708',34.99,'https://example.com/product_1731173708'),(27,'Test Product_1731173875','Updated product description for timestamp 1731173875',34.99,'https://example.com/product_1731173875'),(28,'Test Product_1731174320','Updated product description for timestamp 1731174320',34.99,'https://example.com/product_1731174320'),(29,'Test Product_1731174444','Updated product description for timestamp 1731174444',34.99,'https://example.com/product_1731174444'),(30,'Test Product_1731174523','Updated product description for timestamp 1731174523',34.99,'https://example.com/product_1731174523'),(31,'Test Product_1731174578','Updated product description for timestamp 1731174578',34.99,'https://example.com/product_1731174578'),(32,'Test Product_1731175117','Updated product description for timestamp 1731175117',34.99,'https://example.com/product_1731175117'),(33,'Test Product_1731175247','Updated product description for timestamp 1731175247',34.99,'https://example.com/product_1731175247'),(34,'Test Product_1731175685','Updated product description for timestamp 1731175685',34.99,'https://example.com/product_1731175685'),(35,'Test Product_1731177516','Updated product description for timestamp 1731177516',34.99,'https://example.com/product_1731177516'),(36,'Test Product_1731178110','Updated product description for timestamp 1731178110',34.99,'https://example.com/product_1731178110'),(37,'Test Product_1731178234','Updated product description for timestamp 1731178234',34.99,'https://example.com/product_1731178234'),(38,'Test Product_1731179008','Updated product description for timestamp 1731179008',34.99,'https://example.com/product_1731179008');
/*!40000 ALTER TABLE `Product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Quiz_Result`
--

DROP TABLE IF EXISTS `Quiz_Result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Quiz_Result` (
  `result_id` int NOT NULL AUTO_INCREMENT,
  `quiz_id` int NOT NULL,
  `recommended_species` varchar(50) DEFAULT NULL,
  `recommended_breed` varchar(50) DEFAULT NULL,
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `trait_preferences` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`result_id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `Quiz_Result_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `Starting_Quiz` (`quiz_id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Quiz_Result`
--

LOCK TABLES `Quiz_Result` WRITE;
/*!40000 ALTER TABLE `Quiz_Result` DISABLE KEYS */;
INSERT INTO `Quiz_Result` VALUES (14,14,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-07 22:12:23'),(15,15,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-07 22:33:32'),(16,16,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-07 22:35:10'),(17,17,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-08 21:45:16'),(18,18,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-08 21:46:43'),(19,19,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-08 21:48:56'),(20,20,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-08 21:54:18'),(21,21,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-08 22:00:55'),(22,22,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-08 22:03:33'),(23,23,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 02:15:13'),(24,24,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 02:19:35'),(25,25,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 02:28:27'),(26,26,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 02:52:59'),(27,27,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 02:58:47'),(28,28,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 03:05:53'),(29,29,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 03:09:32'),(30,30,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 03:13:19'),(31,31,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 03:16:43'),(32,32,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 03:18:30'),(33,33,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 03:19:42'),(34,34,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 03:23:47'),(35,35,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 03:27:27'),(36,36,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 03:31:21'),(37,37,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 03:32:34'),(38,38,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 03:34:35'),(39,39,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 03:36:26'),(40,40,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 04:17:41'),(41,41,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 04:18:25'),(42,42,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 04:27:13'),(43,43,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 04:29:09'),(44,44,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 04:32:57'),(45,45,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 04:34:07'),(46,46,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 04:36:10'),(47,47,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 04:39:08'),(48,48,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 04:41:22'),(49,49,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 04:42:02'),(50,50,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 04:44:15'),(51,51,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 04:48:40'),(52,52,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 04:50:35'),(53,53,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 17:04:36'),(54,54,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 17:18:18'),(55,55,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 17:20:35'),(56,56,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 17:21:26'),(57,57,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 17:23:20'),(58,58,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 17:32:09'),(59,59,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 17:33:31'),(60,60,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 17:35:08'),(61,61,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 17:37:55'),(62,62,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 17:45:20'),(63,63,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 17:47:25'),(64,64,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 17:48:43'),(65,65,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 17:49:39'),(66,66,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 17:58:38'),(67,67,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 18:00:47'),(68,68,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 18:08:05'),(69,69,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 18:38:37'),(70,70,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 18:48:30'),(71,71,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 18:50:35'),(72,72,'dog',NULL,100.00,'[{\"trait\": \"High Energy\", \"value\": \"binary\"}, {\"trait\": \"Easily Trained\", \"value\": \"binary\"}]','2024-11-09 19:03:29');
/*!40000 ALTER TABLE `Quiz_Result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Shelter`
--

DROP TABLE IF EXISTS `Shelter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Shelter` (
  `shelter_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `is_no_kill` tinyint(1) NOT NULL,
  PRIMARY KEY (`shelter_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=202 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Shelter`
--

LOCK TABLES `Shelter` WRITE;
/*!40000 ALTER TABLE `Shelter` DISABLE KEYS */;
INSERT INTO `Shelter` VALUES (3,'Happy Paws Shelter','123 Pet Street, Anytown, ST 12345','555-123-4567','contact@happypaws.example.com',1),(38,'Test Shelter','123 Test St','555-0123','test@shelter.com',1),(39,'Test Shelter','123 Test St','555-0123','shelter@test.com',1),(40,'Test Pet Shelter','456 Pet St','555-0124','pet@shelter.com',1),(43,'Test Shelter_1731019326','123 Test St','555-0123','shelter_1731019326@test.com',1),(44,'Test Pet Shelter_1731019326','456 Pet St','555-0124','pet_1731019326@shelter.com',1),(45,'Test Shelter_1731096703','123 Test St','555-0123','shelter_1731096703@test.com',1),(46,'Test Pet Shelter_1731096703','456 Pet St','555-0124','pet_1731096703@shelter.com',1),(47,'Test Shelter_1731101692','123 Test St','555-0123','shelter_1731101692@test.com',1),(48,'Test Pet Shelter_1731101692','456 Pet St','555-0124','pet_1731101692@shelter.com',1),(49,'Test Shelter_1731101919','123 Test St','555-0123','shelter_1731101919@test.com',1),(50,'Test Pet Shelter_1731101919','456 Pet St','555-0124','pet_1731101919@shelter.com',1),(51,'Test Shelter_1731102178','123 Test St','555-0123','shelter_1731102178@test.com',1),(52,'Test Pet Shelter_1731102178','456 Pet St','555-0124','pet_1731102178@shelter.com',1),(53,'Test Shelter_1731102316','123 Test St','555-0123','shelter_1731102316@test.com',1),(54,'Test Pet Shelter_1731102316','456 Pet St','555-0124','pet_1731102316@shelter.com',1),(55,'Test Shelter_1731102403','123 Test St','555-0123','shelter_1731102403@test.com',1),(56,'Test Pet Shelter_1731102403','456 Pet St','555-0124','pet_1731102403@shelter.com',1),(57,'Test Shelter_1731102536','123 Test St','555-0123','shelter_1731102536@test.com',1),(58,'Test Pet Shelter_1731102536','456 Pet St','555-0124','pet_1731102536@shelter.com',1),(59,'Test Shelter_1731102858','123 Test St','555-0123','shelter_1731102858@test.com',1),(60,'Test Pet Shelter_1731102858','456 Pet St','555-0124','pet_1731102858@shelter.com',1),(61,'Test Shelter_1731103070','123 Test St','555-0123','shelter_1731103070@test.com',1),(62,'Test Pet Shelter_1731103070','456 Pet St','555-0124','pet_1731103070@shelter.com',1),(63,'Test Shelter_1731103255','123 Test St','555-0123','shelter_1731103255@test.com',1),(64,'Test Pet Shelter_1731103255','456 Pet St','555-0124','pet_1731103255@shelter.com',1),(65,'Test Shelter_1731103412','123 Test St','555-0123','shelter_1731103412@test.com',1),(66,'Test Pet Shelter_1731103412','456 Pet St','555-0124','pet_1731103412@shelter.com',1),(67,'Test Shelter_1731118512','123 Test St','555-0123','shelter_1731118512@test.com',1),(68,'Test Pet Shelter_1731118512','456 Pet St','555-0124','pet_1731118512@shelter.com',1),(69,'Test Shelter_1731118775','123 Test St','555-0123','shelter_1731118775@test.com',1),(70,'Test Pet Shelter_1731118775','456 Pet St','555-0124','pet_1731118775@shelter.com',1),(71,'Test Shelter_1731119307','123 Test St','555-0123','shelter_1731119307@test.com',1),(72,'Test Pet Shelter_1731119307','456 Pet St','555-0124','pet_1731119307@shelter.com',1),(73,'Test Shelter_1731120779','123 Test St','555-0123','shelter_1731120779@test.com',1),(74,'Test Pet Shelter_1731120779','456 Pet St','555-0124','pet_1731120779@shelter.com',1),(75,'Test Shelter_1731121127','123 Test St','555-0123','shelter_1731121127@test.com',1),(76,'Test Pet Shelter_1731121127','456 Pet St','555-0124','pet_1731121127@shelter.com',1),(77,'Test Shelter_1731121553','123 Test St','555-0123','shelter_1731121553@test.com',1),(78,'Test Pet Shelter_1731121553','456 Pet St','555-0124','pet_1731121553@shelter.com',1),(79,'Test Shelter_1731121771','123 Test St','555-0123','shelter_1731121771@test.com',1),(80,'Test Pet Shelter_1731121771','456 Pet St','555-0124','pet_1731121771@shelter.com',1),(81,'Test Shelter_1731121999','123 Test St','555-0123','shelter_1731121999@test.com',1),(82,'Test Pet Shelter_1731121999','456 Pet St','555-0124','pet_1731121999@shelter.com',1),(83,'Test Shelter_1731122202','123 Test St','555-0123','shelter_1731122202@test.com',1),(84,'Test Pet Shelter_1731122202','456 Pet St','555-0124','pet_1731122202@shelter.com',1),(85,'Test Shelter_1731122309','123 Test St','555-0123','shelter_1731122309@test.com',1),(86,'Test Pet Shelter_1731122309','456 Pet St','555-0124','pet_1731122309@shelter.com',1),(87,'Test Shelter_1731122381','123 Test St','555-0123','shelter_1731122381@test.com',1),(88,'Test Pet Shelter_1731122381','456 Pet St','555-0124','pet_1731122381@shelter.com',1),(89,'Test Shelter_1731122627','123 Test St','555-0123','shelter_1731122627@test.com',1),(90,'Test Pet Shelter_1731122627','456 Pet St','555-0124','pet_1731122627@shelter.com',1),(91,'Test Shelter_1731122846','123 Test St','555-0123','shelter_1731122846@test.com',1),(92,'Test Pet Shelter_1731122846','456 Pet St','555-0124','pet_1731122846@shelter.com',1),(93,'Test Shelter_1731123080','123 Test St','555-0123','shelter_1731123080@test.com',1),(94,'Test Pet Shelter_1731123080','456 Pet St','555-0124','pet_1731123080@shelter.com',1),(95,'Test Shelter_1731123154','123 Test St','555-0123','shelter_1731123154@test.com',1),(96,'Test Pet Shelter_1731123154','456 Pet St','555-0124','pet_1731123154@shelter.com',1),(97,'Test Shelter_1731123275','123 Test St','555-0123','shelter_1731123275@test.com',1),(98,'Test Pet Shelter_1731123275','456 Pet St','555-0124','pet_1731123275@shelter.com',1),(99,'Test Shelter_1731123385','123 Test St','555-0123','shelter_1731123385@test.com',1),(100,'Test Pet Shelter_1731123385','456 Pet St','555-0124','pet_1731123385@shelter.com',1),(101,'Test Shelter_1731125861','123 Test St','555-0123','shelter_1731125861@test.com',1),(102,'Test Pet Shelter_1731125861','456 Pet St','555-0124','pet_1731125861@shelter.com',1),(103,'Test Shelter_1731125904','123 Test St','555-0123','shelter_1731125904@test.com',1),(104,'Test Pet Shelter_1731125904','456 Pet St','555-0124','pet_1731125904@shelter.com',1),(105,'Test Shelter_1731126433','123 Test St','555-0123','shelter_1731126433@test.com',1),(106,'Test Pet Shelter_1731126433','456 Pet St','555-0124','pet_1731126433@shelter.com',1),(107,'Image Test Shelter_1731126433','789 Image St','555-0125','image_1731126433@shelter.com',1),(108,'Test Shelter_1731126548','123 Test St','555-0123','shelter_1731126548@test.com',1),(109,'Test Pet Shelter_1731126548','456 Pet St','555-0124','pet_1731126548@shelter.com',1),(110,'Image Test Shelter_1731126548','789 Image St','555-0125','image_1731126548@shelter.com',1),(111,'Test Shelter_1731126776','123 Test St','555-0123','shelter_1731126776@test.com',1),(112,'Test Pet Shelter_1731126776','456 Pet St','555-0124','pet_1731126776@shelter.com',1),(113,'Image Test Shelter_1731126776','789 Image St','555-0125','image_1731126776@shelter.com',1),(114,'Test Shelter_1731126846','123 Test St','555-0123','shelter_1731126846@test.com',1),(115,'Test Pet Shelter_1731126846','456 Pet St','555-0124','pet_1731126846@shelter.com',1),(116,'Image Test Shelter_1731126846','789 Image St','555-0125','image_1731126846@shelter.com',1),(117,'Test Shelter_1731126970','123 Test St','555-0123','shelter_1731126970@test.com',1),(118,'Test Pet Shelter_1731126970','456 Pet St','555-0124','pet_1731126970@shelter.com',1),(119,'Image Test Shelter_1731126970','789 Image St','555-0125','image_1731126970@shelter.com',1),(120,'Test Shelter_1731127148','123 Test St','555-0123','shelter_1731127148@test.com',1),(121,'Test Pet Shelter_1731127148','456 Pet St','555-0124','pet_1731127148@shelter.com',1),(122,'Image Test Shelter_1731127148','789 Image St','555-0125','image_1731127148@shelter.com',1),(123,'Test Shelter_1731127282','123 Test St','555-0123','shelter_1731127282@test.com',1),(124,'Test Pet Shelter_1731127282','456 Pet St','555-0124','pet_1731127282@shelter.com',1),(125,'Image Test Shelter_1731127282','789 Image St','555-0125','image_1731127282@shelter.com',1),(126,'Test Shelter_1731127322','123 Test St','555-0123','shelter_1731127322@test.com',1),(127,'Test Pet Shelter_1731127322','456 Pet St','555-0124','pet_1731127322@shelter.com',1),(128,'Image Test Shelter_1731127322','789 Image St','555-0125','image_1731127322@shelter.com',1),(129,'Test Shelter_1731127455','123 Test St','555-0123','shelter_1731127455@test.com',1),(130,'Test Pet Shelter_1731127455','456 Pet St','555-0124','pet_1731127455@shelter.com',1),(131,'Image Test Shelter_1731127455','789 Image St','555-0125','image_1731127455@shelter.com',1),(132,'Test Shelter_1731127719','123 Test St','555-0123','shelter_1731127719@test.com',1),(133,'Test Pet Shelter_1731127719','456 Pet St','555-0124','pet_1731127719@shelter.com',1),(134,'Image Test Shelter_1731127719','789 Image St','555-0125','image_1731127719@shelter.com',1),(135,'Test Shelter_1731127835','123 Test St','555-0123','shelter_1731127835@test.com',1),(136,'Test Pet Shelter_1731127835','456 Pet St','555-0124','pet_1731127835@shelter.com',1),(137,'Image Test Shelter_1731127835','789 Image St','555-0125','image_1731127835@shelter.com',1),(138,'Test Shelter_1731171875','123 Test St','555-0123','shelter_1731171875@test.com',1),(139,'Test Pet Shelter_1731171875','456 Pet St','555-0124','pet_1731171875@shelter.com',1),(140,'Image Test Shelter_1731171875','789 Image St','555-0125','image_1731171875@shelter.com',1),(141,'Test Shelter_1731172697','123 Test St','555-0123','shelter_1731172697@test.com',1),(142,'Test Pet Shelter_1731172697','456 Pet St','555-0124','pet_1731172697@shelter.com',1),(143,'Image Test Shelter_1731172697','789 Image St','555-0125','image_1731172697@shelter.com',1),(144,'Test Shelter_1731172835','123 Test St','555-0123','shelter_1731172835@test.com',1),(145,'Test Pet Shelter_1731172835','456 Pet St','555-0124','pet_1731172835@shelter.com',1),(146,'Image Test Shelter_1731172835','789 Image St','555-0125','image_1731172835@shelter.com',1),(147,'Test Shelter_1731172885','123 Test St','555-0123','shelter_1731172885@test.com',1),(148,'Test Pet Shelter_1731172885','456 Pet St','555-0124','pet_1731172885@shelter.com',1),(149,'Image Test Shelter_1731172885','789 Image St','555-0125','image_1731172885@shelter.com',1),(150,'Test Shelter_1731173000','123 Test St','555-0123','shelter_1731173000@test.com',1),(151,'Test Pet Shelter_1731173000','456 Pet St','555-0124','pet_1731173000@shelter.com',1),(152,'Image Test Shelter_1731173000','789 Image St','555-0125','image_1731173000@shelter.com',1),(153,'Test Shelter_1731173528','123 Test St','555-0123','shelter_1731173528@test.com',1),(154,'Test Pet Shelter_1731173528','456 Pet St','555-0124','pet_1731173528@shelter.com',1),(155,'Image Test Shelter_1731173528','789 Image St','555-0125','image_1731173528@shelter.com',1),(156,'Test Shelter_1731173610','123 Test St','555-0123','shelter_1731173610@test.com',1),(157,'Test Pet Shelter_1731173610','456 Pet St','555-0124','pet_1731173610@shelter.com',1),(158,'Image Test Shelter_1731173610','789 Image St','555-0125','image_1731173610@shelter.com',1),(159,'Test Shelter_1731173708','123 Test St','555-0123','shelter_1731173708@test.com',1),(160,'Test Pet Shelter_1731173708','456 Pet St','555-0124','pet_1731173708@shelter.com',1),(161,'Image Test Shelter_1731173708','789 Image St','555-0125','image_1731173708@shelter.com',1),(162,'Test Shelter_1731173875','123 Test St','555-0123','shelter_1731173875@test.com',1),(163,'Test Pet Shelter_1731173875','456 Pet St','555-0124','pet_1731173875@shelter.com',1),(164,'Image Test Shelter_1731173875','789 Image St','555-0125','image_1731173875@shelter.com',1),(165,'Test Shelter_1731174320','123 Test St','555-0123','shelter_1731174320@test.com',1),(166,'Test Pet Shelter_1731174320','456 Pet St','555-0124','pet_1731174320@shelter.com',1),(167,'Image Test Shelter_1731174320','789 Image St','555-0125','image_1731174320@shelter.com',1),(168,'Test Shelter_1731174444','123 Test St','555-0123','shelter_1731174444@test.com',1),(169,'Test Pet Shelter_1731174444','456 Pet St','555-0124','pet_1731174444@shelter.com',1),(170,'Image Test Shelter_1731174444','789 Image St','555-0125','image_1731174444@shelter.com',1),(171,'Test Shelter_1731174523','123 Test St','555-0123','shelter_1731174523@test.com',1),(172,'Test Pet Shelter_1731174523','456 Pet St','555-0124','pet_1731174523@shelter.com',1),(173,'Image Test Shelter_1731174523','789 Image St','555-0125','image_1731174523@shelter.com',1),(174,'Test Shelter_1731174578','123 Test St','555-0123','shelter_1731174578@test.com',1),(175,'Test Pet Shelter_1731174578','456 Pet St','555-0124','pet_1731174578@shelter.com',1),(176,'Image Test Shelter_1731174578','789 Image St','555-0125','image_1731174578@shelter.com',1),(177,'Test Shelter_1731175117','123 Test St','555-0123','shelter_1731175117@test.com',1),(178,'Test Pet Shelter_1731175117','456 Pet St','555-0124','pet_1731175117@shelter.com',1),(179,'Image Test Shelter_1731175117','789 Image St','555-0125','image_1731175117@shelter.com',1),(180,'Test Shelter_1731175247','123 Test St','555-0123','shelter_1731175247@test.com',1),(181,'Test Pet Shelter_1731175247','456 Pet St','555-0124','pet_1731175247@shelter.com',1),(182,'Image Test Shelter_1731175247','789 Image St','555-0125','image_1731175247@shelter.com',1),(183,'Test Shelter_1731175685','123 Test St','555-0123','shelter_1731175685@test.com',1),(184,'Test Pet Shelter_1731175685','456 Pet St','555-0124','pet_1731175685@shelter.com',1),(185,'Image Test Shelter_1731175685','789 Image St','555-0125','image_1731175685@shelter.com',1),(186,'Test Shelter_1731177516','123 Test St','555-0123','shelter_1731177516@test.com',1),(187,'Test Pet Shelter_1731177516','456 Pet St','555-0124','pet_1731177516@shelter.com',1),(188,'Test Shelter_1731178110','123 Test St','555-0123','shelter_1731178110@test.com',1),(189,'Test Pet Shelter_1731178110','456 Pet St','555-0124','pet_1731178110@shelter.com',1),(190,'Test Shelter_1731178234','123 Test St','555-0123','shelter_1731178234@test.com',1),(191,'Test Pet Shelter_1731178234','456 Pet St','555-0124','pet_1731178234@shelter.com',1),(192,'Test Shelter_1731178398','123 Test St','555-0123','shelter_1731178398@test.com',1),(193,'Test Pet Shelter_1731178398','456 Pet St','555-0124','pet_1731178398@shelter.com',1),(200,'Test Shelter_1731179008','123 Test St','555-0123','shelter_1731179008@test.com',1),(201,'Test Pet Shelter_1731179008','456 Pet St','555-0124','pet_1731179008@shelter.com',1);
/*!40000 ALTER TABLE `Shelter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Starting_Quiz`
--

DROP TABLE IF EXISTS `Starting_Quiz`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Starting_Quiz` (
  `quiz_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `quiz_date` date NOT NULL,
  PRIMARY KEY (`quiz_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `Starting_Quiz_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Starting_Quiz`
--

LOCK TABLES `Starting_Quiz` WRITE;
/*!40000 ALTER TABLE `Starting_Quiz` DISABLE KEYS */;
INSERT INTO `Starting_Quiz` VALUES (14,1,'2024-11-07'),(15,1,'2024-11-07'),(16,1,'2024-11-07'),(17,1,'2024-11-08'),(18,1,'2024-11-08'),(19,1,'2024-11-08'),(20,1,'2024-11-08'),(21,1,'2024-11-08'),(22,1,'2024-11-08'),(23,1,'2024-11-08'),(24,1,'2024-11-08'),(25,1,'2024-11-08'),(26,1,'2024-11-08'),(27,1,'2024-11-08'),(28,1,'2024-11-08'),(29,1,'2024-11-08'),(30,1,'2024-11-08'),(31,1,'2024-11-08'),(32,1,'2024-11-08'),(33,1,'2024-11-08'),(34,1,'2024-11-08'),(35,1,'2024-11-08'),(36,1,'2024-11-08'),(37,1,'2024-11-08'),(38,1,'2024-11-08'),(39,1,'2024-11-08'),(40,1,'2024-11-08'),(41,1,'2024-11-08'),(42,1,'2024-11-08'),(43,1,'2024-11-08'),(44,1,'2024-11-08'),(45,1,'2024-11-08'),(46,1,'2024-11-08'),(47,1,'2024-11-08'),(48,1,'2024-11-08'),(49,1,'2024-11-08'),(50,1,'2024-11-08'),(51,1,'2024-11-08'),(52,1,'2024-11-08'),(53,1,'2024-11-09'),(54,1,'2024-11-09'),(55,1,'2024-11-09'),(56,1,'2024-11-09'),(57,1,'2024-11-09'),(58,1,'2024-11-09'),(59,1,'2024-11-09'),(60,1,'2024-11-09'),(61,1,'2024-11-09'),(62,1,'2024-11-09'),(63,1,'2024-11-09'),(64,1,'2024-11-09'),(65,1,'2024-11-09'),(66,1,'2024-11-09'),(67,1,'2024-11-09'),(68,1,'2024-11-09'),(69,1,'2024-11-09'),(70,1,'2024-11-09'),(71,1,'2024-11-09'),(72,1,'2024-11-09');
/*!40000 ALTER TABLE `Starting_Quiz` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Trait_Category`
--

DROP TABLE IF EXISTS `Trait_Category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Trait_Category` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Trait_Category`
--

LOCK TABLES `Trait_Category` WRITE;
/*!40000 ALTER TABLE `Trait_Category` DISABLE KEYS */;
INSERT INTO `Trait_Category` VALUES (1,'energy_level','Activity and exercise needs'),(2,'independence','Ability to be left alone'),(3,'social_needs','Level of human interaction needed'),(4,'maintenance','Grooming and care requirements'),(5,'trainability','Ease of training'),(6,'child_friendly','Compatibility with children'),(7,'pet_friendly','Compatibility with other pets'),(8,'noise_level','Typical noise production'),(9,'space_needs','Required living space'),(10,'health_needs','Special health considerations'),(12,'social','Social characteristics'),(13,'training','Training characteristics');
/*!40000 ALTER TABLE `Trait_Category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `User`
--

DROP TABLE IF EXISTS `User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `User` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `registration_date` date NOT NULL,
  `role` enum('adopter','shelter_staff','admin') NOT NULL DEFAULT 'adopter',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `account_status` enum('pending','active','suspended') DEFAULT 'pending',
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`account_status`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `User`
--

LOCK TABLES `User` WRITE;
/*!40000 ALTER TABLE `User` DISABLE KEYS */;
INSERT INTO `User` VALUES (1,'testuser','test@example.com','$2y$10$fiLCnjD7eAYTpkRkME.ngeH3lMFnwaZkbjQu7zb1B4Bv4U.6Z/JY6','2024-11-05','adopter',NULL,'pending',NULL),(3,'testuser2','test2@example.com','$2y$10$OCeeg4WvIsnbbhchGylNC.nbLBSgUmpU.bFk45Q8B.0lGYAoe4kk6','2024-11-05','adopter',NULL,'pending',NULL),(5,'testuser_1731019326','test_1731019326@example.com','$2y$10$CNHOyAnK3tNg8ZW96v5CkOGsD/f0ho1jcB2SiiuCHXh2J4zCQn9K2','2024-11-07','adopter',NULL,'pending',NULL),(6,'testuser_1731096703','test_1731096703@example.com','$2y$10$wWq73N4MzNp0oURSLFnb.egm5SUVXu29jHtFD/kKFUohRy4s1KY7m','2024-11-08','adopter',NULL,'pending',NULL),(7,'testuser_1731101692','test_1731101692@example.com','$2y$10$6F3NKVAdu4txPDtIRxHDC.lOh4tEDrAhCrHIkmnX3uEMyum7y8qLa','2024-11-08','adopter',NULL,'pending',NULL),(8,'testuser_1731101919','test_1731101919@example.com','$2y$10$YS1db4ZUSySb3WJ4atNGf.dTnl3wRb5grplXxxpbRKWdGiJcfNvTG','2024-11-08','adopter',NULL,'pending',NULL),(9,'testuser_1731102178','test_1731102178@example.com','$2y$10$1Ca.WXwDBRb6yO20SXhjeuQCyIaQL/AVzwcHKoHItorH3QqO5e8v6','2024-11-08','adopter',NULL,'pending',NULL),(10,'testuser_1731102316','test_1731102316@example.com','$2y$10$uKD2jes1XdzrIC3qgbiXBeVFCalfY5/mLJVHI92G77vvgKmzc8Dn6','2024-11-08','adopter',NULL,'pending',NULL),(11,'testuser_1731102403','test_1731102403@example.com','$2y$10$WwBv/AKHruw7gik1qgQSuexIQSLxXDu.eU0F0wo8/uQPCS2I77REm','2024-11-08','adopter',NULL,'pending',NULL),(12,'testuser_1731102536','test_1731102536@example.com','$2y$10$ITHfW.ZEw.9KGit2UzTjV.Mm3.BS4URdxXjTOVc9ElBYEHjzjte3e','2024-11-08','adopter',NULL,'pending',NULL),(13,'testuser_1731102730','test_1731102730@example.com','$2y$10$5o8qe0nEy7.aoEQ3FnVy9uPDjRDeJGHGCB9FKjYDmzEAnXZBQd.V2','2024-11-08','adopter',NULL,'pending',NULL),(14,'testuser_1731102858','test_1731102858@example.com','$2y$10$JoCptoD/z043.f7iBtmRk.sNVQ3PrI5ZnxYzsnuHLunXTnlA1D5KC','2024-11-08','adopter',NULL,'pending',NULL),(15,'testuser_1731103070','test_1731103070@example.com','$2y$10$Rm/Xd5EKptLKJZx/CTe8n.mAGTzawDkROYzZM3eMh3Ewv0f5/JwDK','2024-11-08','adopter',NULL,'pending',NULL),(16,'testuser_1731103255','test_1731103255@example.com','$2y$10$IPv72/kFDNFrySmpf8R1b.lPriOixM7DNz4qvbBfo9Yku92/kyDAe','2024-11-08','adopter',NULL,'pending',NULL),(17,'testuser_1731103412','test_1731103412@example.com','$2y$10$X3BPkajpD.QOgx59a0KR6uOilbJTsN2pcpmnd/7/daBf43QgQAQKu','2024-11-08','adopter',NULL,'pending',NULL),(18,'testuser_1731118512','test_1731118512@example.com','$2y$10$Crse7bUHGAvLdv74BBUEAeKbMo69Ih1uahZvgSnPLipL/wHNiuaii','2024-11-08','adopter',NULL,'pending',NULL),(19,'testuser_1731118775','test_1731118775@example.com','$2y$10$EWcv7rqIaAsfgY7b87wzrODRmK5KJ3JlNXns110/jcLghFKYs/R4y','2024-11-08','adopter',NULL,'pending',NULL),(20,'testuser_1731119307','test_1731119307@example.com','$2y$10$u7v.J52Q59VIeUwkdejWaOVwTh8UfH7Mt2mfpV0C4idXybBDeqdz.','2024-11-08','adopter',NULL,'pending',NULL),(21,'testuser_1731120779','test_1731120779@example.com','$2y$10$j2DraUGVO8vor1GLCUHi5OgTmVtPiGfe4RmNFOe93mA2a4aXyiq8O','2024-11-08','adopter',NULL,'pending',NULL),(22,'testuser_1731121127','test_1731121127@example.com','$2y$10$PepE3e1NRVFBW6y.7HxDVug17FgVnS1fyP3iV8fwyAlzCVVtg8qZq','2024-11-08','adopter',NULL,'pending',NULL),(23,'testuser_1731121553','test_1731121553@example.com','$2y$10$gTNro.K5OixJQV3/pGtCu.1u.xiJVsb4TQavCMSBIGWO49k7HZqFO','2024-11-08','adopter',NULL,'pending',NULL),(24,'testuser_1731121771','test_1731121771@example.com','$2y$10$52LDHUl2HWp5kdRfVA.XIerxrjGTVw89SnOaIN0V.BYb0pSddsoOK','2024-11-08','adopter',NULL,'pending',NULL),(25,'testuser_1731121999','test_1731121999@example.com','$2y$10$UOFTHXCU1TPx5jp5cVvRgujWsgTg5h4GMfBZbkh/F5EV/yKIeTZrC','2024-11-08','adopter',NULL,'pending',NULL),(26,'testuser_1731122202','test_1731122202@example.com','$2y$10$bOYKATUmU4aGgIZStRaToeHJWMft3hNCy2f0QHzYgLkMkKJl.vxfe','2024-11-08','adopter',NULL,'pending',NULL),(27,'testuser_1731122309','test_1731122309@example.com','$2y$10$GlY1c.WG2PLvNShYziPhNeJhjvvqlzSoRhaEoqci5ODzFdnmn1csu','2024-11-08','adopter',NULL,'pending',NULL),(28,'testuser_1731122381','test_1731122381@example.com','$2y$10$vt3.VcII04n53XAffxuUw.bpQLOemqXV2DW3fAcKNn9FFjq3FoyDS','2024-11-08','adopter',NULL,'pending',NULL),(29,'testuser_1731122627','test_1731122627@example.com','$2y$10$2ihOkDJ0yuOnuJBaTC3TLOjWTOnDrel25kFv7rsNYqz44srGMD2uq','2024-11-08','adopter',NULL,'pending',NULL),(30,'testuser_1731122846','test_1731122846@example.com','$2y$10$tQ2RwClHqPMAVBo.HwKYzeFIMWjlGupMfc6pBcel8kge5ps188n1.','2024-11-08','adopter',NULL,'pending',NULL),(31,'testuser_1731123080','test_1731123080@example.com','$2y$10$Yzc43INmAkVZVGpnrBL3FuVWB3CKFclExk.m0PIS.YIPXnYIl2GBG','2024-11-08','adopter',NULL,'pending',NULL),(32,'testuser_1731123154','test_1731123154@example.com','$2y$10$uKVOPpNdLrKCHnUuKCrwjeZSjS5TK0YR/Ex94M/CYGWQYuT570you','2024-11-08','adopter',NULL,'pending',NULL),(33,'testuser_1731123275','test_1731123275@example.com','$2y$10$UKyaZ540XG/mdQVyK1HQtuipDAgzqgKbZFoybMpZCa0xIV2EKoqTC','2024-11-08','adopter',NULL,'pending',NULL),(34,'testuser_1731123385','test_1731123385@example.com','$2y$10$DUPJJotmBo5rnmzMe8/e4OISbzT7/DxsaR3dnl.mo4zGanljDrhMK','2024-11-08','adopter',NULL,'pending',NULL),(35,'testuser_1731125861','test_1731125861@example.com','$2y$10$nrCdtsYw4g2uNNtTSSPw0esm32zDdgRN/QaR4l4i1DBzM6gZUog0m','2024-11-08','adopter',NULL,'pending',NULL),(36,'testuser_1731125904','test_1731125904@example.com','$2y$10$hU9JEphgGfjLMNhAJ/Zf5uKSSSfHgXdQnmA0CQy7fdxHMD.RITvYa','2024-11-08','adopter',NULL,'pending',NULL),(37,'testuser_1731126433','test_1731126433@example.com','$2y$10$o..MXVWhkMmOkggKpjed.exQrVrf2/yxEM570A9wya0o12dMgt0pu','2024-11-08','adopter',NULL,'pending',NULL),(38,'testuser_1731126548','test_1731126548@example.com','$2y$10$KPv8lSd2GvN3obgmY0OrIOY1lRH42b0tiatBqq5BkGLwPlVUgUp5S','2024-11-08','adopter',NULL,'pending',NULL),(39,'testuser_1731126776','test_1731126776@example.com','$2y$10$J15tQUZgvL6WLemAXOnRhOoogZgf1kIs4F88VPQ.qM9apKaulkRUq','2024-11-08','adopter',NULL,'pending',NULL),(40,'testuser_1731126846','test_1731126846@example.com','$2y$10$qaES/6ahXvetzfa1Yfn8TOQEtfBDBQHWiT7HTh6hLT.FeB073hWyi','2024-11-08','adopter',NULL,'pending',NULL),(41,'testuser_1731126970','test_1731126970@example.com','$2y$10$b0gK2qBTIGoZYDPHO/9DGeowfqU6DtaU.KDwufpqc8PuYDIux39NG','2024-11-08','adopter',NULL,'pending',NULL),(42,'testuser_1731127148','test_1731127148@example.com','$2y$10$Wu5iJbXjk1093mRBimmoCeRmHZGYwDM3FpZOFxNntSL38UhdIG89a','2024-11-08','adopter',NULL,'pending',NULL),(43,'testuser_1731127282','test_1731127282@example.com','$2y$10$WDJZbOP0fo/AzNqkZ8B93eVdJ/vZcQdsZ2AvhhGws/UoP2zbxIJ2.','2024-11-08','adopter',NULL,'pending',NULL),(44,'testuser_1731127322','test_1731127322@example.com','$2y$10$8Oz23EBIcz2hbyJ.cNBYT.a8XHHhUgqIhh8hj8VrJ4aJ/jgNy2UlW','2024-11-08','adopter',NULL,'pending',NULL),(45,'testuser_1731127455','test_1731127455@example.com','$2y$10$vbwoVfNFLSyMK./U1xX/heqVrmAm1lNXayu.zu1InhdfyE1gHRdwu','2024-11-08','adopter',NULL,'pending',NULL),(46,'testuser_1731127719','test_1731127719@example.com','$2y$10$6WAJQuwcVgDn6zk5CrEj6.3pI2kr4O6SId5FJxhM8.6BPhZ9AawP.','2024-11-08','adopter',NULL,'pending',NULL),(47,'testuser_1731127835','test_1731127835@example.com','$2y$10$BGD3FPHU9cwIAfzeeoRzGuBLrhvyJ9LSQiwdTebPA9PJJECF6l6zS','2024-11-08','adopter',NULL,'pending',NULL),(48,'testuser_1731171875','test_1731171875@example.com','$2y$10$FViCnqs9UM7p3csgwESTcuUqI6AlsJaF0wzFT/.4106HUgG4W9FpO','2024-11-09','adopter',NULL,'pending',NULL),(49,'testuser_1731172697','test_1731172697@example.com','$2y$10$/F4bfQgabLtEo6DZ26rZ0uKc5RXFscjw0/G.CVCaSeullE0309UzC','2024-11-09','adopter',NULL,'pending',NULL),(50,'testuser_1731172835','test_1731172835@example.com','$2y$10$RMos1sdfhJZ76sm9Dl4DN.pzBsQjc7pXZHD6JuSbi2ORaCaaOu4Nm','2024-11-09','adopter',NULL,'pending',NULL),(51,'testuser_1731172885','test_1731172885@example.com','$2y$10$bezdQB.NVcNb9K2QHbX.De8M0YZrEarbthVOgrodYOE9o8DNQPyMq','2024-11-09','adopter',NULL,'pending',NULL),(52,'testuser_1731173000','test_1731173000@example.com','$2y$10$wftxXra1BN0QxawaYxBkLuwd6aGolH79KDFxG5v3jXAvhXYkztT6e','2024-11-09','adopter',NULL,'pending',NULL),(53,'testuser_1731173528','test_1731173528@example.com','$2y$10$0MHWku2V.waHuErnQy.uxu7wzJ.GRotLIx.geYrfdl7HlCnL2awGq','2024-11-09','adopter',NULL,'pending',NULL),(54,'testuser_1731173610','test_1731173610@example.com','$2y$10$ry4K3cfeZIM.8f8z5hUubu5uVz2Svx1y35gtMz0EkW8D9l4sgKxvy','2024-11-09','adopter',NULL,'pending',NULL),(55,'testuser_1731173708','test_1731173708@example.com','$2y$10$ckrPlmwOlIKUVtOpe7Cx.OQ2XJRfJ2AfT2A2DkZJ/OFxxqMLygtJq','2024-11-09','adopter',NULL,'pending',NULL),(56,'testuser_1731173875','test_1731173875@example.com','$2y$10$226fo/Tq/zLbnUV3BUsN0uOu2VUrnGgGTKwtusTrmSaVy3ZcI7djm','2024-11-09','adopter',NULL,'pending',NULL),(57,'testuser_1731174320','test_1731174320@example.com','$2y$10$JyaOKLSrOIfyd9.oqgvMKOrZiyH72/uPpb/VyJK2gzHvTRM0GX24S','2024-11-09','adopter',NULL,'pending',NULL),(58,'testuser_1731174444','test_1731174444@example.com','$2y$10$uCIjPN86wDzRAZztd2HKJue7NDUMN5oeXle0iJVhXEtavqHekEO86','2024-11-09','adopter',NULL,'pending',NULL),(59,'testuser_1731174523','test_1731174523@example.com','$2y$10$5EJL0goeoX/TU5R8jrRdD.bGKdrwc7i1C7DdwBJ72tnIY7j8vf6f6','2024-11-09','adopter',NULL,'pending',NULL),(60,'testuser_1731174578','test_1731174578@example.com','$2y$10$ISGUV4X/sFvsvV5PLTolMenWhspsbaMRQe.vgQ6hkR0383UtWNNFm','2024-11-09','adopter',NULL,'pending',NULL),(61,'testuser_1731175117','test_1731175117@example.com','$2y$10$Vs3/9HpR8.I2NPd1d4CADu8o5DzQTty6wTq0t7LzL42zuobeHXv/e','2024-11-09','adopter',NULL,'pending',NULL),(62,'testuser_1731175247','test_1731175247@example.com','$2y$10$cCngLlO2tpky3fGiQzg1Xum1YrMNJzo6W.l8rwffI4X.t7DR9pWNq','2024-11-09','adopter',NULL,'pending',NULL),(63,'testuser_1731175685','test_1731175685@example.com','$2y$10$cDLaS5y3bVo/tLp3VjouKemUau.5G4jm6et2CiU0l8yk2ha1S.pOK','2024-11-09','adopter',NULL,'pending',NULL),(64,'testuser_1731177516','test_1731177516@example.com','$2y$10$j4sqWMG/j0ZpKCuBq5q/GeDJ.xfIkDYWJiAMfpa689ZEa4XDQr5Pe','2024-11-09','adopter',NULL,'pending',NULL),(65,'testuser_1731178110','test_1731178110@example.com','$2y$10$64bCJgmeyIayXQHhsxLRcetIMnP1nGxnuzQYCxDctQ/bQoMXf8mLy','2024-11-09','adopter',NULL,'pending',NULL),(66,'testuser_1731178234','test_1731178234@example.com','$2y$10$YLeFOmOGhP/33AIknplXiezz6eJOyqDcfrT30YZwS7IT2AWCPendK','2024-11-09','adopter',NULL,'pending',NULL),(67,'testuser_1731178398','test_1731178398@example.com','$2y$10$dO/tmdAd.i3KuoEEPiwbruqVRoxEYnkn4usSbmXz3x7UZH7Aka.8y','2024-11-09','adopter',NULL,'pending',NULL),(73,'testuser_1731179008','test_1731179008@example.com','$2y$10$.K2VpvIIlrkg.Tyf/VukZeAPuOc5yaGAW9ublnL37jtlK2qRDlU9G','2024-11-09','adopter',NULL,'pending',NULL),(74,'woodmtaylor','test@gmail.com','$2y$10$ID5TL7OYpUpzXbShFJW0a.XSrOGLuJdx7Wu3z/oVr7NpBMHUonVbm','2024-11-10','adopter',NULL,'pending',NULL),(75,'asdfasdf','test@test.com','$2y$10$Jr/nzjbaAHu4JKLFTf6jgefWyzssCXcbMXn.3gWAI78fmP2yUNuyS','2024-11-10','adopter',NULL,'pending',NULL),(76,'Taylor Test','test1@gmail.com','$2y$10$bRixW9/P0.61xc4DrDYiXuQTWHPkepxYyPka/Lv2b4YTE9ZDYoGcm','2024-11-10','adopter',NULL,'pending',NULL),(77,'Hey123','woodmtaylor@gmail.com','$2y$10$zDGIKYraHvA33a40e3xh0.FXI2dzo6E7.LigyIPve7u4fsoIZsyfO','2024-11-11','adopter',NULL,'pending',NULL);
/*!40000 ALTER TABLE `User` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `UserProfile`
--

DROP TABLE IF EXISTS `UserProfile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `UserProfile` (
  `profile_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `housing_type` enum('house','apartment','condo','other') DEFAULT NULL,
  `has_yard` tinyint(1) DEFAULT NULL,
  `other_pets` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`profile_id`),
  KEY `idx_user_profile` (`user_id`),
  CONSTRAINT `UserProfile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `UserProfile`
--

LOCK TABLES `UserProfile` WRITE;
/*!40000 ALTER TABLE `UserProfile` DISABLE KEYS */;
/*!40000 ALTER TABLE `UserProfile` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-11-11 16:00:27
