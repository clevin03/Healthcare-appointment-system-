<?php

class DoctorDirectory {
	public static function buildDatabaseContext($conn, $patientId) {
		$context = "\n\n[AVAILABLE DATA IN SYSTEM]\n";

		$doctors = self::getAllDoctors($conn);
		if (!empty($doctors)) {
			$context .= "Available Doctors:\n";
			foreach ($doctors as $doc) {
				$context .= "- " . $doc['doctor_name'] . " (" . ($doc['department_name'] ?? 'N/A') . ")\n";
			}
		}

		$appointments = self::getPatientAppointments($conn, $patientId);
		if (!empty($appointments)) {
			$context .= "\nPatient's Upcoming Appointments:\n";
			foreach ($appointments as $apt) {
				$context .= "- " . $apt['appointment_date'] . " at " . $apt['appointment_time'] . " with Dr. " . ($apt['doctor_name'] ?? 'N/A') . "\n";
			}
		} else {
			$context .= "\nPatient has no upcoming appointments.\n";
		}

		return $context;
	}

	public static function getAllDoctors($conn) {
		$sql = "SELECT d.*, dep.department_name 
				FROM doctors d 
				LEFT JOIN departments dep ON d.department_id = dep.department_id 
				WHERE d.status = 'ACTIVE'
				ORDER BY d.doctor_id DESC
				LIMIT 20";

		$result = $conn->query($sql);
		$doctors = [];

		if ($result) {
			while ($row = $result->fetch_assoc()) {
				$doctors[] = $row;
			}
		}

		return $doctors;
	}

	public static function getDoctorsBySpecialty($conn, $specialty) {
		$specialty = '%' . $specialty . '%';

		$sql = "SELECT d.*, dep.department_name 
				FROM doctors d 
				LEFT JOIN departments dep ON d.department_id = dep.department_id 
				WHERE (dep.department_name LIKE ? OR d.doctor_name LIKE ?)
				AND d.status = 'ACTIVE'
				ORDER BY d.doctor_id DESC
				LIMIT 10";

		$stmt = $conn->prepare($sql);
		if (!$stmt) {
			return [];
		}

		$stmt->bind_param("ss", $specialty, $specialty);
		$stmt->execute();
		$result = $stmt->get_result();

		$doctors = [];
		while ($row = $result->fetch_assoc()) {
			$doctors[] = $row;
		}

		$stmt->close();
		return $doctors;
	}

	public static function getPatientAppointments($conn, $patientId) {
		$sql = "SELECT a.*, d.doctor_name, dep.department_name 
				FROM appointments a 
				LEFT JOIN doctors d ON a.doctor_id = d.doctor_id 
				LEFT JOIN departments dep ON a.department_id = dep.department_id 
				WHERE a.patient_id = ?
				AND a.status IN ('PENDING', 'CONFIRMED')
				ORDER BY a.appointment_date DESC
				LIMIT 10";

		$stmt = $conn->prepare($sql);
		if (!$stmt) {
			return [];
		}

		$stmt->bind_param("i", $patientId);
		$stmt->execute();
		$result = $stmt->get_result();

		$appointments = [];
		while ($row = $result->fetch_assoc()) {
			$appointments[] = $row;
		}

		$stmt->close();
		return $appointments;
	}

	public static function buildDoctorTable($doctors) {
		if (empty($doctors)) {
			return '<p>No doctors available.</p>';
		}

		$html = '<table class="data-table"><thead><tr><th>Doctor</th><th>Department</th><th>Status</th></tr></thead><tbody>';
		foreach ($doctors as $doctor) {
			$html .= '<tr>'
				. '<td>' . htmlspecialchars($doctor['doctor_name'] ?? '') . '</td>'
				. '<td>' . htmlspecialchars($doctor['department_name'] ?? 'N/A') . '</td>'
				. '<td>' . htmlspecialchars($doctor['status'] ?? 'Unknown') . '</td>'
				. '</tr>';
		}
		$html .= '</tbody></table>';
		return $html;
	}

	public static function buildAppointmentTable($appointments) {
		if (empty($appointments)) {
			return '<p>No appointments available.</p>';
		}

		$html = '<table class="data-table"><thead><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Status</th></tr></thead><tbody>';
		foreach ($appointments as $appointment) {
			$html .= '<tr>'
				. '<td>' . htmlspecialchars($appointment['appointment_date'] ?? '') . '</td>'
				. '<td>' . htmlspecialchars($appointment['appointment_time'] ?? '') . '</td>'
				. '<td>' . htmlspecialchars($appointment['doctor_name'] ?? 'N/A') . '</td>'
				. '<td>' . htmlspecialchars($appointment['status'] ?? 'Unknown') . '</td>'
				. '</tr>';
		}
		$html .= '</tbody></table>';
		return $html;
	}
}
