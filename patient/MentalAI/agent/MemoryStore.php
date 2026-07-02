<?php

class MemoryStore {
	public static function remember($conn, $patientId, $memoryType, $memoryValue, $consentGiven = 0, $sensitivityLevel = 'low', $expiresAt = null) {
		ConversationLogger::ensureTables($conn);

		$sql = "INSERT INTO patient_memory (patient_id, memory_type, memory_value, consent_given, sensitivity_level, expires_at)
				VALUES (?, ?, ?, ?, ?, ?)";
		$stmt = $conn->prepare($sql);
		if (!$stmt) {
			return false;
		}

		$stmt->bind_param('ississ', $patientId, $memoryType, $memoryValue, $consentGiven, $sensitivityLevel, $expiresAt);
		$result = $stmt->execute();
		$stmt->close();

		return $result;
	}

	public static function recallRecent($conn, $patientId, $limit = 5) {
		ConversationLogger::ensureTables($conn);

		$sql = "SELECT memory_type, memory_value, sensitivity_level, created_at
				FROM patient_memory
				WHERE patient_id = ?
				AND (expires_at IS NULL OR expires_at > NOW())
				AND consent_given = 1
				ORDER BY created_at DESC
				LIMIT ?";
		$stmt = $conn->prepare($sql);
		if (!$stmt) {
			return [];
		}

		$stmt->bind_param('ii', $patientId, $limit);
		$stmt->execute();
		$result = $stmt->get_result();

		$memory = [];
		while ($row = $result->fetch_assoc()) {
			$memory[] = $row;
		}

		$stmt->close();
		return $memory;
	}
}
