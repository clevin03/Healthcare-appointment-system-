<?php

class ResponseEngine {
	public static function handlePatternMatching($message, $patientId, $conn, &$actions) {
		$response = '';

		if (preg_match('/(find|show|looking for|need).*(doctor|physician|cardiologist|neurologist|dermatologist|pediatrician)/i', $message)) {
			preg_match('/(cardiologist|neurologist|dermatologist|pediatrician|surgeon|dentist|ophthalmologist|psychiatrist)/i', $message, $matches);
			$specialty = $matches[0] ?? 'any';
			$doctors = DoctorDirectory::getDoctorsBySpecialty($conn, $specialty);

			if (!empty($doctors)) {
				$response = "Great! I found " . count($doctors) . " doctor(s) for you:\n\n";
				$response .= DoctorDirectory::buildDoctorTable($doctors);
				$actions = [
					['label' => 'Book Appointment', 'action' => 'I want to book an appointment'],
					['label' => 'Contact Doctor', 'action' => 'Show me contact details']
				];
			} else {
				$response = "I couldn't find doctors for '$specialty'. Would you like to view all doctors?";
				$actions = [
					['label' => 'View All Doctors', 'action' => 'Show me all doctors']
				];
			}
		} else if (preg_match('/(show|list|view).*(all\s)?doctors|all\s+doctors/i', $message)) {
			$doctors = DoctorDirectory::getAllDoctors($conn);
			if (!empty($doctors)) {
				$response = "Here are all available doctors:\n\n";
				$response .= DoctorDirectory::buildDoctorTable($doctors);
				$actions = [
					['label' => 'Book Appointment', 'action' => 'I want to book an appointment']
				];
			} else {
				$response = "No doctors are currently available.";
			}
		} else if (preg_match('/(view|show|list|my).*(appointment|booking)/i', $message)) {
			$appointments = DoctorDirectory::getPatientAppointments($conn, $patientId);
			if (!empty($appointments)) {
				$response = "Here are your appointments:\n\n";
				$response .= DoctorDirectory::buildAppointmentTable($appointments);
				$actions = [
					['label' => 'Book New', 'action' => 'Book an appointment']
				];
			} else {
				$response = "You don't have any appointments. Would you like to book one?";
				$actions = [
					['label' => 'Book Appointment', 'action' => 'Book an appointment']
				];
			}
		} else if (preg_match('/(stress|anxiety|depress|panic|mental health|therapy|counseling)/i', $message)) {
			$doctors = DoctorDirectory::getDoctorsBySpecialty($conn, 'psychiatrist');
			$response = "Thank you for sharing this. You are not alone.\n\n";
			$response .= "I can help you connect with a mental health professional. ";

			if (!empty($doctors)) {
				$response .= "Here are available specialists:\n\n" . DoctorDirectory::buildDoctorTable($doctors);
			} else {
				$response .= "I can guide you to book the earliest available consultation.";
			}

			$actions = [
				['label' => 'Find Psychiatrist', 'action' => 'Find psychiatrist'],
				['label' => 'Book Counseling', 'action' => 'Book counseling appointment'],
				['label' => 'Self-Care Steps', 'action' => 'Show self-care steps']
			];
		} else {
			$response = "I'm here to help! I can assist you with:\n• Finding doctors\n• Booking appointments\n• Viewing your schedule\n• Health information\n\nWhat would you like to do?";
			$actions = [
				['label' => 'Find a Doctor', 'action' => 'Find a doctor'],
				['label' => 'Book Appointment', 'action' => 'Book an appointment'],
				['label' => 'View Appointments', 'action' => 'Show my appointments']
			];
		}

		return $response;
	}

	public static function getContextualActions($message, $patientId, $conn) {
		$actions = [];

		if (preg_match('/(doctor|specialist|physician|find)/i', $message)) {
			$actions[] = ['label' => 'Book Appointment', 'action' => 'I want to book an appointment'];
			$actions[] = ['label' => 'Find Another Doctor', 'action' => 'Show me another doctor'];
		}

		if (preg_match('/(appointment|booking|schedule)/i', $message)) {
			$actions[] = ['label' => 'Reschedule', 'action' => 'I need to reschedule'];
			$actions[] = ['label' => 'Book Another', 'action' => 'Book another appointment'];
		}

		if (preg_match('/(health|symptom|advice|tip|wellness)/i', $message)) {
			$actions[] = ['label' => 'Find Doctor', 'action' => 'Find a doctor'];
			$actions[] = ['label' => 'Book Appointment', 'action' => 'Book an appointment'];
		}

		if (empty($actions)) {
			$appointments = DoctorDirectory::getPatientAppointments($conn, $patientId);
			if (empty($appointments)) {
				$actions[] = ['label' => 'Find a Doctor', 'action' => 'Find a doctor'];
				$actions[] = ['label' => 'Book Appointment', 'action' => 'Book an appointment'];
			} else {
				$actions[] = ['label' => 'View Appointments', 'action' => 'Show my appointments'];
				$actions[] = ['label' => 'Book Another', 'action' => 'Book another appointment'];
			}
		}

		return array_slice($actions, 0, 3);
	}

	public static function mergeActions($primaryActions, $extraActions) {
		$all = array_merge($primaryActions, $extraActions);
		$unique = [];
		$seen = [];

		foreach ($all as $action) {
			$key = strtolower(($action['label'] ?? '') . '|' . ($action['action'] ?? ''));
			if (!isset($seen[$key])) {
				$seen[$key] = true;
				$unique[] = $action;
			}
		}

		return array_slice($unique, 0, 4);
	}
}
