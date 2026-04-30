-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 05:52 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `appointment_number` varchar(20) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `status` enum('PENDING','CONFIRMED','CANCELLED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `patient_name`, `email`, `phone`, `gender`, `date_of_birth`, `address`, `created_at`) VALUES
(1, 'clevin', NULL, NULL, NULL, NULL, NULL, '2026-02-23 03:38:41'),
(2, 'thiloka', NULL, NULL, NULL, NULL, NULL, '2026-02-23 03:38:41'),
(3, 'kevin', NULL, NULL, NULL, NULL, NULL, '2026-02-23 03:38:41'),
(4, 'clevin', NULL, NULL, NULL, NULL, NULL, '2026-02-23 03:39:09'),
(5, 'thiloka', NULL, NULL, NULL, NULL, NULL, '2026-02-23 03:39:09'),
(6, 'kevin', NULL, NULL, NULL, NULL, NULL, '2026-02-23 03:39:09');

--
-- Dumping data for table `departments` - Add Psychiatry
--

INSERT INTO `departments` (`department_id`, `department_name`, `description`, `is_active`, `created_at`) VALUES
(4, 'Psychiatry', 'Mental health and psychological care', 1, '2026-02-23 03:42:51');

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`doctor_id`, `doctor_name`, `email`, `phone`, `department_id`, `status`, `created_at`) VALUES
(1, 'Dr. Sanjana Patel', 'sanjana.patel@edoctor.com', '555-0101', 1, 'ACTIVE', '2026-02-23 03:43:00'),
(2, 'Dr. Marcus Chen', 'marcus.chen@edoctor.com', '555-0102', 2, 'ACTIVE', '2026-02-23 03:43:00'),
(3, 'Dr. Elena Rossi', 'elena.rossi@edoctor.com', '555-0103', 3, 'ACTIVE', '2026-02-23 03:43:00'),
(4, 'Dr. James Wilson', 'james.wilson@edoctor.com', '555-0104', 4, 'ACTIVE', '2026-02-23 03:43:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD UNIQUE KEY `appointment_number` (`appointment_number`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`doctor_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

-- --------------------------------------------------------

--
-- Table structure for table `mental_health_events`
--

CREATE TABLE `mental_health_events` (
  `event_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `risk_level` varchar(20) NOT NULL,
  `category` varchar(50) NOT NULL,
  `matched_keyword` varchar(100) DEFAULT NULL,
  `user_message` text DEFAULT NULL,
  `escalated` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_history`
--

CREATE TABLE `chat_history` (
  `chat_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `user_message` text DEFAULT NULL,
  `bot_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient_memory`
--

CREATE TABLE `patient_memory` (
  `memory_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `memory_type` varchar(50) NOT NULL,
  `memory_value` text NOT NULL,
  `consent_given` tinyint(1) DEFAULT 0,
  `sensitivity_level` varchar(20) DEFAULT 'low',
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Indexes for table `mental_health_events`
--

ALTER TABLE `mental_health_events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `idx_patient_id` (`patient_id`),
  ADD KEY `idx_risk_level` (`risk_level`);

--
-- Indexes for table `chat_history`
--

ALTER TABLE `chat_history`
  ADD PRIMARY KEY (`chat_id`),
  ADD KEY `idx_chat_patient_id` (`patient_id`);

--
-- Indexes for table `patient_memory`
--

ALTER TABLE `patient_memory`
  ADD PRIMARY KEY (`memory_id`),
  ADD KEY `idx_memory_patient_id` (`patient_id`),
  ADD KEY `idx_memory_type` (`memory_type`);

-- --------------------------------------------------------

--
-- AUTO_INCREMENT for table `mental_health_events`
--

ALTER TABLE `mental_health_events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_history`
--

ALTER TABLE `chat_history`
  MODIFY `chat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient_memory`
--

ALTER TABLE `patient_memory`
  MODIFY `memory_id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Constraints for table `mental_health_events`
--

ALTER TABLE `mental_health_events`
  ADD CONSTRAINT `mental_health_events_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`);

--
-- Constraints for table `chat_history`
--

ALTER TABLE `chat_history`
  ADD CONSTRAINT `chat_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`);

--
-- Constraints for table `patient_memory`
--

ALTER TABLE `patient_memory`
  ADD CONSTRAINT `patient_memory_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
