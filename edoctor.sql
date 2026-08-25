-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 25, 2026 at 08:32 AM
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
  `admin_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `user_id`, `admin_name`) VALUES
(1, 5, 'Admin1');

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
  `session_id` int DEFAULT NULL,
  `status` enum('CONFIRMED','CANCELLED','COMPLETED') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'CONFIRMED',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`appointment_id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  KEY `idx_appointments_session` (`session_id`),
  KEY `appointment_number` (`appointment_number`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `appointment_number`, `patient_id`, `doctor_id`, `session_id`, `status`, `created_at`) VALUES
(20, '1', 11, 10, 19, 'CONFIRMED', '2026-08-11 06:28:59'),
(21, '1', 11, 11, 20, 'CONFIRMED', '2026-08-13 05:16:24'),
(22, '2', 10, 10, 19, 'CONFIRMED', '2026-08-18 01:41:11'),
(23, '2', 10, 11, 20, 'CONFIRMED', '2026-08-18 01:41:32'),
(24, '1', 10, 11, 23, 'CONFIRMED', '2026-08-22 01:13:29'),
(25, '1', 10, 11, 24, 'CONFIRMED', '2026-08-22 01:19:55'),
(27, '1', 11, 11, 26, 'CONFIRMED', '2026-08-25 04:58:25'),
(28, '2', 10, 11, 26, 'CONFIRMED', '2026-08-25 04:58:51');

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
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') COLLATE utf8mb4_general_ci DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`doctor_id`),
  KEY `department_id` (`department_id`),
  KEY `fk_doctors_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`doctor_id`, `doctor_name`, `phone`, `department_id`, `status`, `created_at`, `user_id`) VALUES
(10, 'upul', '0111222333', 1, 'ACTIVE', '2026-07-29 13:00:31', 10),
(11, 'Sanath Nishantha', '0999888777', 3, 'ACTIVE', '2026-08-10 15:41:33', 11);

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

DROP TABLE IF EXISTS `medical_records`;
CREATE TABLE IF NOT EXISTS `medical_records` (
  `appointment_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `record_id` int NOT NULL AUTO_INCREMENT,
  `diagnosis` varchar(200) DEFAULT NULL,
  `prescription` varchar(200) DEFAULT NULL,
  `notes` varchar(200) DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`record_id`),
  KEY `fk_mr_appointment` (`appointment_id`),
  KEY `fk_mr_patient` (`patient_id`),
  KEY `fk_mr_doctor` (`doctor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `medical_records`
--

INSERT INTO `medical_records` (`appointment_id`, `patient_id`, `doctor_id`, `record_id`, `diagnosis`, `prescription`, `notes`, `date`) VALUES
(20, 11, 10, 5, 'test 1', 'test des', 'test notes', '2026-08-19 10:32:38'),
(22, 10, 10, 6, 'test 2', 'test des', 'test notes', '2026-08-19 10:33:19'),
(25, 10, 11, 7, 'Test', 'Parasitamol', 'Mukuth na', '2026-08-22 01:20:50'),
(27, 11, 11, 8, '', '', '', '2026-08-25 05:02:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `created_by` int DEFAULT NULL COMMENT 'admin_id who created this session',
  `status` enum('active','pending') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `current_count` int NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `fk_sessions_doctor` (`doctor_id`),
  KEY `fk_sessions_admin` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`session_id`, `doctor_id`, `session_day`, `start_time`, `end_time`, `max_patients`, `created_by`, `status`, `created_at`, `current_count`) VALUES
(16, 10, '2026-07-31', '22:35:00', '23:35:00', 10, 1, 'active', '2026-07-29 13:02:31', 0),
(17, 10, '2026-08-03', '15:00:00', '17:00:00', 20, 1, 'active', '2026-07-31 16:00:19', 0),
(18, 10, '2026-08-05', '12:20:00', '13:20:00', 10, 1, 'active', '2026-08-03 04:48:32', 0),
(19, 10, '2026-08-19', '18:00:00', '18:00:00', 10, 1, 'active', '2026-08-04 10:26:39', 2),
(20, 11, '2026-08-21', '16:00:00', '17:30:00', 15, 1, 'active', '2026-08-10 15:42:28', 2),
(23, 11, '2026-08-24', '16:59:00', '17:00:00', 15, 1, 'active', '2026-08-22 01:10:55', 2),
(24, 11, '2026-08-22', '08:00:00', '08:30:00', 5, 1, 'active', '2026-08-22 01:19:32', 1),
(25, 11, '2026-08-28', '16:15:00', '16:15:00', 20, 1, 'active', '2026-08-24 00:44:43', 0),
(26, 11, '2026-08-25', '10:15:00', '10:30:00', 5, 1, 'active', '2026-08-25 04:32:46', 2);

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `user_type`, `is_active`, `created_at`) VALUES
(1, 'clevinfer@gmail.com', '$2y$10$iRyUR1.mzuy6RHDjF8WFkOHIKIxP823S.y9lS8Hw5ZsxsL1QmlH12', 'patient', 1, '2026-05-08 23:12:52'),
(2, 'samarapala@gmail.com', '$2y$10$3gQMpckT4sCayXfErq2kf.CLD6434h0lCFrDYk/H/48OciE.y9z/a', 'patient', 1, '2026-05-09 01:33:12'),
(3, 'kamal@hotmail.com', '$2y$10$StAov5eJzirUimJC.sgBHOvoh7Wzf9FkA8W3yn4sywI4YVxy1coMW', 'patient', 1, '2026-05-09 02:07:08'),
(4, 'namal@gmail.com', '$2y$10$Gfs5B.QoYHgQ23nTkQv4pemF5DO0bmGAP0HPSxWu0kFfXulXyDYvK', 'patient', 1, '2026-05-09 04:39:47'),
(5, 'admin@gmail.com', '$2b$10$6m4Wy3r8lrDpywvsNECuDuphsB1hg0Lekmgm1KgLn9GRKaUn.juYe', 'admin', 1, '2026-05-16 04:17:10'),
(6, 'kevin@gmail.com', '$2y$10$Wc.KwH55RetMccEOS.6SQOIQ/S3WKi/npgkoqhXCrWbScR2kH6lme', 'patient', 1, '2026-06-26 05:40:01'),
(10, 'upul@gmail.com', '$2y$10$CX2KJFRK/dZEvPF62T2dDeF14ANQxVhCdlQL3JJCYXTeuSFlfMqai', 'doctor', 1, '2026-07-29 13:00:31'),
(11, 'sanath@gmail.com', '$2y$10$L0koXowhyFSCuQ.EH9joE.qAPqlyTzFoPJg7OkftidApSnrHCcckW', 'doctor', 1, '2026-08-10 15:41:33');

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
  ADD CONSTRAINT `fk_appointments_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`);

--
-- Constraints for table `chat_history`
--
ALTER TABLE `chat_history`
  ADD CONSTRAINT `chat_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`);

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `fk_doctors_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `fk_mr_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`),
  ADD CONSTRAINT `fk_mr_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`),
  ADD CONSTRAINT `fk_mr_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`);

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

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`admin_id`),
  ADD CONSTRAINT `fk_sessions_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
