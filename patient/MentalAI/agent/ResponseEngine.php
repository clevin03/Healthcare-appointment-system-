<?php

class ResponseEngine {
	public static function handlePatternMatching($message, $patientId, $conn, &$actions, $conversationHistory = []) {
		$response = '';
		$normalized = strtolower(trim((string) $message));

		if (preg_match('/(self\s*-?care|coping|calm down|breathing|grounding|relax|sleep tips)/i', $message)) {
			$response = self::buildSelfCareResponse();
			$actions = [
				['label' => 'Find Psychiatrist', 'action' => 'Find psychiatrist'],
				['label' => 'I Need Better Sleep', 'action' => 'I cannot sleep well these days']
			];
			return $response;
		}

		if (preg_match('/(need|want|schedule).*(counsel|therapy|psychiatrist|mental health)/i', $message)
			|| preg_match('/(psychiatrist|counseling|therapy).*(help|visit|support)/i', $message)) {
			$doctors = DoctorDirectory::getDoctorsBySpecialty($conn, 'psychiatrist');
			if (!empty($doctors)) {
				$response = "I can help you connect with a mental health specialist right away.\n\nHere are available psychiatrists/counselors:\n\n" . DoctorDirectory::buildDoctorTable($doctors) . "\n\nTell me which doctor you prefer, and I will guide you to the next step.";
				$actions = [
					['label' => 'Show My Appointments', 'action' => 'Show my appointments'],
					['label' => 'Self-Care For Now', 'action' => 'Show self-care steps']
				];
			} else {
				$response = "I understand. I could not find a psychiatrist right now, but I can still help you look for the right mental health support.";
				$actions = [
					['label' => 'View All Doctors', 'action' => 'Show me all doctors'],
					['label' => 'Self-Care Steps', 'action' => 'Show self-care steps']
				];
			}
			return $response;
		}

		if (preg_match('/(stress|anxiety|depress|panic|mental health|therapy|counseling|burnout|overwhelmed|lonely|worried|sad)/i', $message)) {
			$response = self::buildMentalCheckInResponse($normalized, $conversationHistory);
			$actions = [
				['label' => 'Find Psychiatrist', 'action' => 'Find psychiatrist'],
				['label' => 'Book Counseling', 'action' => 'Book counseling appointment'],
				['label' => 'Show Self-Care Plan', 'action' => 'Show self-care steps']
			];
			return $response;
		}

		if (preg_match('/(find|show|looking for|need).*(doctor|physician|cardiologist|neurologist|dermatologist|pediatrician|surgeon|dentist|ophthalmologist|psychiatrist)/i', $message)) {
			preg_match('/(cardiologist|neurologist|dermatologist|pediatrician|surgeon|dentist|ophthalmologist|psychiatrist)/i', $message, $matches);
			$specialty = $matches[0] ?? 'any';
			$doctors = DoctorDirectory::getDoctorsBySpecialty($conn, $specialty);

			if (!empty($doctors)) {
				$response = "Great! I found " . count($doctors) . " doctor(s) for you:\n\n" . DoctorDirectory::buildDoctorTable($doctors);
				$actions = [
					['label' => 'Show My Appointments', 'action' => 'Show my appointments']
				];
			} else {
				$response = "I could not find doctors for '" . $specialty . "'. Would you like to view all doctors?";
				$actions = [
					['label' => 'View All Doctors', 'action' => 'Show me all doctors'],
					['label' => 'Show My Appointments', 'action' => 'Show my appointments']
				];
			}
			return $response;
		}

		if (preg_match('/(show|list|view).*(all\s)?doctors|all\s+doctors/i', $message)) {
			$doctors = DoctorDirectory::getAllDoctors($conn);
			if (!empty($doctors)) {
				$response = "Here are all available doctors:\n\n" . DoctorDirectory::buildDoctorTable($doctors);
				$actions = [
					['label' => 'Show My Appointments', 'action' => 'Show my appointments']
				];
			} else {
				$response = "No doctors are currently available.";
			}
			return $response;
		}

		if (preg_match('/(view|show|list|my).*(appointment|booking)/i', $message)) {
			$appointments = DoctorDirectory::getPatientAppointments($conn, $patientId);
			if (!empty($appointments)) {
				$response = "Here are your upcoming appointment details:\n\n" . DoctorDirectory::buildAppointmentTable($appointments);
				$actions = [
					['label' => 'Find Doctor', 'action' => 'Find a doctor']
				];
			} else {
				$response = "You do not have any appointments yet. Would you like me to help you find a doctor?";
				$actions = [
					['label' => 'Find Doctor', 'action' => 'Find a doctor']
				];
			}
			return $response;
		}

		$response = '';
		// We rely on the AI model to handle general questions, medication info, etc.
		// Returning empty response/actions means the orchestrator will just use the AI's response.
		return $response;
	}

	private static function buildSelfCareResponse() {
		return "Thank you for asking. Let us do a simple plan for the next 10 minutes:\n\n"
			. "1. Breathe slowly: inhale 4 sec, hold 4 sec, exhale 6 sec, repeat 5 times.\n"
			. "2. Grounding: name 5 things you see, 4 you feel, 3 you hear, 2 you smell, 1 you taste.\n"
			. "3. Body reset: drink water and relax your shoulders/jaw for 30 seconds.\n"
			. "4. Thought check: write one worry and one small action you can do today.\n"
			. "5. Connection: message one trusted person.\n\n"
			. "If you want, tell me what is hardest right now (sleep, anxiety, overthinking, or sadness), and I will guide you personally.";
	}

	private static function buildMentalCheckInResponse($normalizedMessage, $conversationHistory) {
		if (strpos($normalizedMessage, 'sleep') !== false || strpos($normalizedMessage, 'cannot sleep') !== false) {
			return "I hear you. Sleep struggles can feel exhausting.\n\nTonight, try this: no screen for 30 minutes before bed, dim lights, slow breathing for 5 minutes, and keep your room cool/quiet.\n\nWould you like a bedtime routine plan, or should I help you book a counseling session?";
		}

		if (strpos($normalizedMessage, 'anxiety') !== false || strpos($normalizedMessage, 'panic') !== false || strpos($normalizedMessage, 'overwhelmed') !== false) {
			return "Thank you for sharing that. You are doing the right thing by talking about it.\n\nCan we do a quick check-in?\n- When do these feelings usually get stronger?\n- Is your body reacting (fast heartbeat, shaking, tight chest)?\n- What has helped you even a little before?\n\nYou can answer in one short line, and I will guide the next step.";
		}

		if (strpos($normalizedMessage, 'sad') !== false || strpos($normalizedMessage, 'depress') !== false || strpos($normalizedMessage, 'lonely') !== false) {
			return "I am really glad you shared this with me. You do not have to carry this alone.\n\nLet us start gently: what has been the heaviest part of your day lately?\n\nIf you prefer, I can also connect you with a mental health doctor now.";
		}

		$lastTurns = is_array($conversationHistory) ? count($conversationHistory) : 0;
		if ($lastTurns > 0) {
			return "I am with you. Thank you for continuing to share.\n\nTell me what you feel most right now: anxiety, sadness, stress, or trouble sleeping.\nBased on your answer, I will give you a focused plan and help with booking if needed.";
		}

		return "Thank you for sharing this. You are not alone.\n\nI can support you in two ways right now:\n- Talk with you step by step like a real check-in\n- Help you quickly book a psychiatrist/counseling appointment\n\nWhich one do you want first?";
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

		if (preg_match('/(mental|stress|anxiety|panic|depress|therapy|counsel)/i', $message)) {
			$actions[] = ['label' => 'Self-Care Steps', 'action' => 'Show self-care steps'];
			$actions[] = ['label' => 'Book Counseling', 'action' => 'Book counseling appointment'];
			$actions[] = ['label' => 'Find Psychiatrist', 'action' => 'Find psychiatrist'];
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
