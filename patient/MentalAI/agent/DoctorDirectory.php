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
			return '<div class="appointment-empty-state"><div class="appointment-empty-icon"><i class="fas fa-calendar-xmark"></i></div><div class="appointment-empty-copy"><h4>No upcoming appointments</h4><p>You do not have any booked appointments right now.</p></div></div>';
		}

		$html = '<div class="appointment-summary"><div class="appointment-summary-label">Upcoming appointments</div><div class="appointment-summary-count">' . count($appointments) . '</div></div><div class="appointment-cards">';
		foreach ($appointments as $appointment) {
			$dateText = self::formatAppointmentDate($appointment['appointment_date'] ?? '');
			$timeText = self::formatAppointmentTime($appointment['appointment_time'] ?? '');
			$status = strtoupper(trim((string) ($appointment['status'] ?? 'UNKNOWN')));
			$statusClass = strtolower($status);

			$html .= '<div class="appointment-card">'
				. '<div class="appointment-card-header">'
				. '<div class="appointment-card-title">Appointment #' . htmlspecialchars((string) ($appointment['appointment_number'] ?? 'N/A')) . '</div>'
				. '<div class="appointment-status status-' . htmlspecialchars($statusClass) . '">' . htmlspecialchars($status) . '</div>'
				. '</div>'
				. '<div class="appointment-card-body">'
				. '<div class="appointment-main">'
				. '<div class="appointment-doctor">' . htmlspecialchars($appointment['doctor_name'] ?? 'N/A') . '</div>'
				. '<div class="appointment-department">' . htmlspecialchars($appointment['department_name'] ?? 'General Practice') . '</div>'
				. '</div>'
				. '<div class="appointment-meta-grid">'
				. '<div class="appointment-meta-item"><span>Date</span><strong>' . htmlspecialchars($dateText) . '</strong></div>'
				. '<div class="appointment-meta-item"><span>Time</span><strong>' . htmlspecialchars($timeText) . '</strong></div>'
				. '</div>'
				. '</div>'
				. '</div>';
		}
		$html .= '</div>';
		return $html;
	}

	private static function formatAppointmentDate($date) {
		if (empty($date)) {
			return 'Not scheduled';
		}

		$dateTime = DateTime::createFromFormat('Y-m-d', $date);
		if ($dateTime instanceof DateTime) {
			return $dateTime->format('M j, Y');
		}

		return (string) $date;
	}

	private static function formatAppointmentTime($time) {
		if (empty($time)) {
			return 'Not set';
		}

		$dateTime = DateTime::createFromFormat('H:i:s', $time) ?: DateTime::createFromFormat('H:i', $time);
		if ($dateTime instanceof DateTime) {
			return $dateTime->format('g:i A');
		}

		return (string) $time;
	}
}
