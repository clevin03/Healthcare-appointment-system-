<?php

class ConversationLogger {
	public static function ensureTables($conn) {
		$createEventTableSQL = "CREATE TABLE IF NOT EXISTS mental_health_events (
			event_id int(11) NOT NULL AUTO_INCREMENT,
			patient_id int(11) NOT NULL,
			risk_level varchar(20) NOT NULL,
			category varchar(50) NOT NULL,
			matched_keyword varchar(100) DEFAULT NULL,
			user_message text,
			escalated tinyint(1) DEFAULT 0,
			created_at timestamp NOT NULL DEFAULT current_timestamp(),
			PRIMARY KEY (event_id),
			KEY idx_patient_id (patient_id),
			KEY idx_risk_level (risk_level),
			CONSTRAINT mental_health_events_ibfk_1 FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
		$conn->query($createEventTableSQL);

		$createChatTableSQL = "CREATE TABLE IF NOT EXISTS chat_history (
			chat_id int(11) NOT NULL AUTO_INCREMENT,
			patient_id int(11) NOT NULL,
			user_message text,
			bot_response text,
			created_at timestamp NOT NULL DEFAULT current_timestamp(),
			PRIMARY KEY (chat_id),
			KEY idx_chat_patient_id (patient_id),
			CONSTRAINT chat_history_ibfk_1 FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
		$conn->query($createChatTableSQL);

		$createMemoryTableSQL = "CREATE TABLE IF NOT EXISTS patient_memory (
			memory_id int(11) NOT NULL AUTO_INCREMENT,
			patient_id int(11) NOT NULL,
			memory_type varchar(50) NOT NULL,
			memory_value text NOT NULL,
			consent_given tinyint(1) DEFAULT 0,
			sensitivity_level varchar(20) DEFAULT 'low',
			expires_at datetime DEFAULT NULL,
			created_at timestamp NOT NULL DEFAULT current_timestamp(),
			PRIMARY KEY (memory_id),
			KEY idx_memory_patient_id (patient_id),
			KEY idx_memory_type (memory_type),
			CONSTRAINT patient_memory_ibfk_1 FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
		$conn->query($createMemoryTableSQL);
	}

	public static function logMentalHealthEvent($conn, $patientId, $userMessage, $riskAssessment, $escalated) {
		if ($riskAssessment['level'] === 'none') {
			return;
		}

		self::ensureTables($conn);

		$sql = "INSERT INTO mental_health_events (patient_id, risk_level, category, matched_keyword, user_message, escalated)
				VALUES (?, ?, ?, ?, ?, ?)";
		$stmt = $conn->prepare($sql);
		if (!$stmt) {
			return;
		}

		$riskLevel = $riskAssessment['level'];
		$category = $riskAssessment['category'];
		$matchedKeyword = $riskAssessment['matched_keyword'];
		$escalatedInt = $escalated ? 1 : 0;

		$stmt->bind_param('issssi', $patientId, $riskLevel, $category, $matchedKeyword, $userMessage, $escalatedInt);
		$stmt->execute();
		$stmt->close();
	}

	public static function saveConversation($conn, $patientId, $userMessage, $botResponse) {
		self::ensureTables($conn);

		$sql = "INSERT INTO chat_history (patient_id, user_message, bot_response) VALUES (?, ?, ?)";
		$stmt = $conn->prepare($sql);
		if ($stmt) {
			$stmt->bind_param("iss", $patientId, $userMessage, $botResponse);
			$stmt->execute();
			$stmt->close();
		}
	}
}
