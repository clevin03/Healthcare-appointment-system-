-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 09, 2026 at 03:21 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `edoctor`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `user_id`) VALUES
(1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `ai_provider_config`
--

DROP TABLE IF EXISTS `ai_provider_config`;
CREATE TABLE IF NOT EXISTS `ai_provider_config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `provider_key` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'ollama, gpt-4o-mini, openai-compatible, dify',
  `label` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `api_url` varchar(500) COLLATE utf8mb4_general_ci DEFAULT '',
  `api_key` varchar(500) COLLATE utf8mb4_general_ci DEFAULT '',
  `model` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '',
  `is_active` tinyint(1) DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_key` (`provider_key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_provider_config`
--

INSERT INTO `ai_provider_config` (`id`, `provider_key`, `label`, `api_url`, `api_key`, `model`, `is_active`, `updated_at`) VALUES
(1, 'ollama', 'Ollama (Local)', 'http://127.0.0.1:11434/api/chat', '', 'llama3.1:8b', 0, '2026-07-03 01:55:44'),
(2, 'gpt-4o-mini', 'OpenAI (gpt-4o-mini)', 'https://api.openai.com/v1/chat/completions', '', 'gpt-4o-mini', 0, '2026-07-03 01:55:44'),
(3, 'openai-compatible', 'OpenAI Compatible (Custom)', 'https://api.openai.com/v1/chat/completions', '', 'gpt-4o-mini', 0, '2026-07-03 01:55:44'),
(4, 'dify', 'Dify', 'https://api.dify.ai/v1/chat-messages', '', '', 0, '2026-07-03 01:55:44');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE IF NOT EXISTS `appointments` (
  `appointment_id` int NOT NULL AUTO_INCREMENT,
  `appointment_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `patient_id` int DEFAULT NULL,
  `doctor_id` int DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `status` enum('PENDING','CONFIRMED','CANCELLED') COLLATE utf8mb4_general_ci DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`appointment_id`),
  UNIQUE KEY `appointment_number` (`appointment_number`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_history`
--

DROP TABLE IF EXISTS `chat_history`;
CREATE TABLE IF NOT EXISTS `chat_history` (
  `chat_id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `user_message` text COLLATE utf8mb4_general_ci,
  `bot_response` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`chat_id`),
  KEY `idx_chat_patient_id` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `department_id` int NOT NULL AUTO_INCREMENT,
  `department_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `description`, `is_active`, `created_at`) VALUES
(1, 'Cardiology', 'Heart and cardiovascular care', 1, '2026-02-23 03:42:51'),
(2, 'Orthopedic', 'Bone and joint care', 1, '2026-02-23 03:42:51'),
(3, 'Neurology', 'Brain and nervous system care', 1, '2026-02-23 03:42:51');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

DROP TABLE IF EXISTS `doctors`;
CREATE TABLE IF NOT EXISTS `doctors` (
  `doctor_id` int NOT NULL AUTO_INCREMENT,
  `doctor_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') COLLATE utf8mb4_general_ci DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`doctor_id`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`doctor_id`, `doctor_name`, `email`, `phone`, `department_id`, `status`, `created_at`) VALUES
(1, 'Senarath', 'senarath@gmail.com', '0710011223', 1, 'ACTIVE', '2026-05-06 11:41:18'),
(2, 'Fernando', 'fernando@gmail.com', '0772233445', 1, 'ACTIVE', '2026-05-06 11:41:18'),
(7, 'Peter', 'peter@gmail.com', '0712244331', 3, 'ACTIVE', '2026-05-10 15:57:17'),
(8, 'santha', 'santha@gmail.com', '0111222333', 2, 'ACTIVE', '2026-07-02 03:46:56');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_sessions`
--

DROP TABLE IF EXISTS `doctor_sessions`;
CREATE TABLE IF NOT EXISTS `doctor_sessions` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `session_title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_id` int NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_patients` int NOT NULL DEFAULT '10',
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`),
  KEY `doctor_id` (`doctor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

DROP TABLE IF EXISTS `medical_records`;
CREATE TABLE IF NOT EXISTS `medical_records` (
  `appointment_id` int DEFAULT NULL,
  `patient_id` int DEFAULT NULL,
  `doctor_id` int DEFAULT NULL,
  `record_id` int NOT NULL AUTO_INCREMENT,
  `diagnosis` varchar(200) DEFAULT NULL,
  `prescription` varchar(200) DEFAULT NULL,
  `notes` varchar(200) DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`record_id`),
  KEY `FK_appointment` (`appointment_id`),
  KEY `FK_patient` (`patient_id`),
  KEY `FK_doctor` (`doctor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mental_health_events`
--

DROP TABLE IF EXISTS `mental_health_events`;
CREATE TABLE IF NOT EXISTS `mental_health_events` (
  `event_id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `risk_level` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `matched_keyword` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_message` text COLLATE utf8mb4_general_ci,
  `escalated` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`),
  KEY `idx_patient_id` (`patient_id`),
  KEY `idx_risk_level` (`risk_level`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
CREATE TABLE IF NOT EXISTS `patients` (
  `patient_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`patient_id`),
  KEY `fk_patient_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `user_id`, `first_name`, `last_name`, `phone`, `gender`, `date_of_birth`, `address`, `created_at`) VALUES
(7, 1, 'Shamith', 'Fernando', '0719773401', 'Male', '2003-03-04', '235 B/2, Thekkawaththa, Demanhandiya', '2026-05-08 23:12:52'),
(8, 2, 'Samarapala', 'Fernando', '0771002003', 'Male', '2001-01-01', 'negombo', '2026-05-09 01:33:12'),
(9, 3, 'Kamal', 'Fernando', '0112233445', 'Male', '2002-02-03', 'katana', '2026-05-09 02:07:08'),
(10, 4, 'namal', 'rajapaksa', '0111222333', 'Male', '1999-03-01', 'colombo', '2026-05-09 04:39:47'),
(11, 6, 'kevin', 'fernando', '0776655443', 'Male', '2003-01-01', 'negombo, Sri Lanka', '2026-06-26 05:40:01');

-- --------------------------------------------------------

--
-- Table structure for table `patient_memory`
--

DROP TABLE IF EXISTS `patient_memory`;
CREATE TABLE IF NOT EXISTS `patient_memory` (
  `memory_id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `memory_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `memory_value` text COLLATE utf8mb4_general_ci NOT NULL,
  `consent_given` tinyint(1) DEFAULT '0',
  `sensitivity_level` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'low',
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`memory_id`),
  KEY `idx_memory_patient_id` (`patient_id`),
  KEY `idx_memory_type` (`memory_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `doctor_id` int DEFAULT NULL,
  `session_day` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_patients` int DEFAULT NULL,
  `status` enum('active','pending') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `FK_doctor` (`doctor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_type` enum('admin','doctor','patient') COLLATE utf8mb4_general_ci DEFAULT 'patient',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `user_type`, `is_active`, `created_at`) VALUES
(1, 'clevinfer@gmail.com', '$2y$10$iRyUR1.mzuy6RHDjF8WFkOHIKIxP823S.y9lS8Hw5ZsxsL1QmlH12', 'patient', 1, '2026-05-08 23:12:52'),
(2, 'samarapala@gmail.com', '$2y$10$3gQMpckT4sCayXfErq2kf.CLD6434h0lCFrDYk/H/48OciE.y9z/a', 'patient', 1, '2026-05-09 01:33:12'),
(3, 'kamal@hotmail.com', '$2y$10$StAov5eJzirUimJC.sgBHOvoh7Wzf9FkA8W3yn4sywI4YVxy1coMW', 'patient', 1, '2026-05-09 02:07:08'),
(4, 'namal@gmail.com', '$2y$10$Gfs5B.QoYHgQ23nTkQv4pemF5DO0bmGAP0HPSxWu0kFfXulXyDYvK', 'patient', 1, '2026-05-09 04:39:47'),
(5, 'admin@gmail.com', '$2b$10$6m4Wy3r8lrDpywvsNECuDuphsB1hg0Lekmgm1KgLn9GRKaUn.juYe', 'admin', 1, '2026-05-16 04:17:10'),
(6, 'kevin@gmail.com', '$2y$10$Wc.KwH55RetMccEOS.6SQOIQ/S3WKi/npgkoqhXCrWbScR2kH6lme', 'patient', 1, '2026-06-26 05:40:01');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `chat_history`
--
ALTER TABLE `chat_history`
  ADD CONSTRAINT `chat_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`);

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `doctor_sessions`
--
ALTER TABLE `doctor_sessions`
  ADD CONSTRAINT `doctor_sessions_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE;

--
-- Constraints for table `mental_health_events`
--
ALTER TABLE `mental_health_events`
  ADD CONSTRAINT `mental_health_events_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`);

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patient_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_memory`
--
ALTER TABLE `patient_memory`
  ADD CONSTRAINT `patient_memory_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
