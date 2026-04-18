<?php

class ResponseEngine {
	public static function handlePatternMatching($message, $patientId, $conn, &$actions, $conversationHistory = [], $responseStyle = 'default') {
		$response = '';
		$normalized = strtolower(trim((string) $message));

		if (preg_match('/(self\s*-?care|coping|calm down|breathing|grounding|relax|sleep tips)/i', $message)) {
			$response = self::buildSelfCareResponse($responseStyle);
			$actions = [
				['label' => 'Book Counseling', 'action' => 'Book counseling appointment'],
				['label' => 'Find Psychiatrist', 'action' => 'Find psychiatrist'],
				['label' => 'I Need Better Sleep', 'action' => 'I cannot sleep well these days']
			];
			return $response;
		}

		if (preg_match('/(book|need|want|schedule).*(counsel|therapy|psychiatrist|mental health)/i', $message)
			|| preg_match('/(psychiatrist|counseling|therapy).*(book|appointment|visit)/i', $message)) {
			$doctors = DoctorDirectory::getDoctorsBySpecialty($conn, 'psychiatrist');
			if (!empty($doctors)) {
				$response = self::styleText(
					"Absolutely. I can help you book a mental health consultation right away.\n\nHere are available psychiatrists/counselors:\n\n" . DoctorDirectory::buildDoctorTable($doctors) . "\n\nTell me which doctor you prefer, and I will guide you to the booking step.",
					"Hari. Mama mental health consultation ekak book karanna help karannam.\n\nMe widihata available psychiatrist / counselor la innawa:\n\n" . DoctorDirectory::buildDoctorTable($doctors) . "\n\nOyata kamathi doctor eka kiyanna, mama booking step ekata guide karannam.",
					$responseStyle
				);
				$actions = [
					['label' => 'Book First Available', 'action' => 'Book the earliest psychiatrist appointment'],
					['label' => 'Show My Appointments', 'action' => 'Show my appointments'],
					['label' => 'Self-Care For Now', 'action' => 'Show self-care steps']
				];
			} else {
				$response = self::styleText(
					"I understand. I could not find a psychiatrist right now, but I can still help you request the earliest mental health appointment.",
					"Mama kiyala thiyenawa. Dan psychiatrist kenek pennanna nathi wenna puluwan, eth mama oyata earliest mental health appointment ekak request karanna help karannam.",
					$responseStyle
				);
				$actions = [
					['label' => 'Book Earliest Appointment', 'action' => 'Book an appointment'],
					['label' => 'View All Doctors', 'action' => 'Show me all doctors'],
					['label' => 'Self-Care Steps', 'action' => 'Show self-care steps']
				];
			}
			return $response;
		}

		if (preg_match('/(stress|anxiety|depress|panic|mental health|therapy|counseling|burnout|overwhelmed|lonely|worried|sad)/i', $message)) {
			$response = self::buildMentalCheckInResponse($normalized, $conversationHistory, $responseStyle);
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
				$response = self::styleText(
					"Great! I found " . count($doctors) . " doctor(s) for you:\n\n" . DoctorDirectory::buildDoctorTable($doctors),
					"Hari! Mama oyata " . count($doctors) . " doctor kenek / keneki laga hoyagaththa.\n\n" . DoctorDirectory::buildDoctorTable($doctors),
					$responseStyle
				);
				$actions = [
					['label' => 'Book Appointment', 'action' => 'I want to book an appointment'],
					['label' => 'Show My Appointments', 'action' => 'Show my appointments']
				];
			} else {
				$response = self::styleText(
					"I could not find doctors for '" . $specialty . "'. Would you like to view all doctors?",
					"'" . $specialty . "' walata doctor kenek dennam nethuwa thiyenawa. Oya all doctors balanna kamathi da?",
					$responseStyle
				);
				$actions = [
					['label' => 'View All Doctors', 'action' => 'Show me all doctors'],
					['label' => 'Book Appointment', 'action' => 'Book an appointment']
				];
			}
			return $response;
		}

		if (preg_match('/(show|list|view).*(all\s)?doctors|all\s+doctors/i', $message)) {
			$doctors = DoctorDirectory::getAllDoctors($conn);
			if (!empty($doctors)) {
				$response = self::styleText(
					"Here are all available doctors:\n\n" . DoctorDirectory::buildDoctorTable($doctors),
					"Mehema available doctor la okkoma inne:\n\n" . DoctorDirectory::buildDoctorTable($doctors),
					$responseStyle
				);
				$actions = [
					['label' => 'Book Appointment', 'action' => 'I want to book an appointment']
				];
			} else {
				$response = self::styleText(
					"No doctors are currently available.",
					"Danhi doctor kenek available na.",
					$responseStyle
				);
			}
			return $response;
		}

		if (preg_match('/(view|show|list|my).*(appointment|booking)/i', $message)) {
			$appointments = DoctorDirectory::getPatientAppointments($conn, $patientId);
			if (!empty($appointments)) {
				$response = self::styleText(
					"Here are your appointments:\n\n" . DoctorDirectory::buildAppointmentTable($appointments),
					"Oyage appointments mehemai:\n\n" . DoctorDirectory::buildAppointmentTable($appointments),
					$responseStyle
				);
				$actions = [
					['label' => 'Book New', 'action' => 'Book an appointment']
				];
			} else {
				$response = self::styleText(
					"You do not have any appointments yet. Would you like to book one now?",
					"Oyata thama appointment ekak naha. Dan book karanna kamathi da?",
					$responseStyle
				);
				$actions = [
					['label' => 'Book Appointment', 'action' => 'Book an appointment'],
					['label' => 'Find Doctor', 'action' => 'Find a doctor']
				];
			}
			return $response;
		}

		$response = self::styleText(
			"I am here with you. We can talk step by step.\n\nWould you like to:\n1) talk about what you are feeling now\n2) get a quick self-care plan\n3) connect with a mental health doctor",
			"Mama oyata ekka innawa. Hita hitatama katha karanna puluwan.\n\nOyata one de monawada?\n1) Dan oya danena de gena katha karanna\n2) ඉක්මන් self-care plan ekak ganna\n3) Mental health doctor kenek laga connect wenna",
			$responseStyle
		);
		$actions = [
			['label' => 'Talk To Me', 'action' => 'I want to talk about what I am feeling'],
			['label' => 'Self-Care Plan', 'action' => 'Show self-care steps'],
			['label' => 'Book Mental Health Doctor', 'action' => 'Book counseling appointment']
		];

		return $response;
	}

	private static function buildSelfCareResponse($responseStyle = 'default') {
		$english = "Thank you for asking. Let us do a simple plan for the next 10 minutes:\n\n"
			. "1. Breathe slowly: inhale 4 sec, hold 4 sec, exhale 6 sec, repeat 5 times.\n"
			. "2. Grounding: name 5 things you see, 4 you feel, 3 you hear, 2 you smell, 1 you taste.\n"
			. "3. Body reset: drink water and relax your shoulders/jaw for 30 seconds.\n"
			. "4. Thought check: write one worry and one small action you can do today.\n"
			. "5. Connection: message one trusted person.\n\n"
			. "If you want, tell me what is hardest right now (sleep, anxiety, overthinking, or sadness), and I will guide you personally.";

		$singlish = "Oya ahapu eka gana thanks. Next 10 minutes walata simple plan ekak karamu:\n\n"
			. "1. හිමින් breathing කරන්න: 4 sec inhale, 4 sec hold, 6 sec exhale - 5 වතාවක්.\n"
			. "2. Grounding: oya දකින්නේ 5 dewal, feel wenne 4 dewal, ahenne 3 dewal, smell 2 dewal, taste 1 ekak kiyanna.\n"
			. "3. Body reset: වතුර ටිකක් බොන්න, shoulder/jaw relax කරන්න.\n"
			. "4. හිතෙන worry ekak ekka, today karanna puluwan පොඩි action ekak ලියාගන්න.\n"
			. "5. Trust කරන kenekta message ekak දාන්න.\n\n"
			. "Sleep, anxiety, overthinking, sadness meken mokakda අමාරු? කියන්න, mama personally guide karannam.";

		return self::styleText($english, $singlish, $responseStyle);
	}

	private static function buildMentalCheckInResponse($normalizedMessage, $conversationHistory, $responseStyle = 'default') {
		if (strpos($normalizedMessage, 'sleep') !== false || strpos($normalizedMessage, 'cannot sleep') !== false) {
			return self::styleText(
				"I hear you. Sleep struggles can feel exhausting.\n\nTonight, try this: no screen for 30 minutes before bed, dim lights, slow breathing for 5 minutes, and keep your room cool/quiet.\n\nWould you like a bedtime routine plan, or should I help you book a counseling session?",
				"Mata terෙනවා. Sleep issue eka harima tiring.\n\nRaatriyata screen use karanne nathuwa, lights dim karala, 5 minutes slow breathing karala, room eka cool/quiet thiyanna try karanna.\n\nBedtime routine ekak one da, nathnam counseling session ekak book karanna help karannada?",
				$responseStyle
			);
		}

		if (strpos($normalizedMessage, 'anxiety') !== false || strpos($normalizedMessage, 'panic') !== false || strpos($normalizedMessage, 'overwhelmed') !== false) {
			return self::styleText(
				"Thank you for sharing that. You are doing the right thing by talking about it.\n\nCan we do a quick check-in?\n- When do these feelings usually get stronger?\n- Is your body reacting (fast heartbeat, shaking, tight chest)?\n- What has helped you even a little before?\n\nYou can answer in one short line, and I will guide the next step.",
				"Meka mata kiyapu eka harima hondai. Katha karana eka hari.\n\nQuick check-in ekak karamu da?\n- Me feelings wadi wenne godak samahara welawata kohomada?\n- Body eka react wenawada? (heartbeat fast, shaking, tight chest)\n- ඉස්සර ටිකක් හරි help වුණේ mokakda?\n\nඑක line ekakෙන් උත්තර දෙන්න, next step eka mama dannam.",
				$responseStyle
			);
		}

		if (strpos($normalizedMessage, 'sad') !== false || strpos($normalizedMessage, 'depress') !== false || strpos($normalizedMessage, 'lonely') !== false) {
			return self::styleText(
				"I am really glad you shared this with me. You do not have to carry this alone.\n\nLet us start gently: what has been the heaviest part of your day lately?\n\nIf you prefer, I can also connect you with a mental health doctor now.",
				"Meka share kala eka harima hondai. Oya eka alone carry karanna ona na.\n\nGentle widihata අහන්නම්: ළඟදී oya diha wadiyatama බර හිතුනේ mokakda?\n\nOne nam mama mental health doctor kenekta connect karannath puluwan.",
				$responseStyle
			);
		}

		$lastTurns = is_array($conversationHistory) ? count($conversationHistory) : 0;
		if ($lastTurns > 0) {
			return self::styleText(
				"I am with you. Thank you for continuing to share.\n\nTell me what you feel most right now: anxiety, sadness, stress, or trouble sleeping.\nBased on your answer, I will give you a focused plan and help with booking if needed.",
				"Mama oyata ekka innawa. Continue කරලා share කරන එකට thanks.\n\nDan oya feel wenne mokakda? anxiety, sadness, stress, නැත්නම් sleep issue ekak da?\nOyage answer eka anuwa mama focused plan ekak dennam, booking help ekath karannam.",
				$responseStyle
			);
		}

		return self::styleText(
			"Thank you for sharing this. You are not alone.\n\nI can support you in two ways right now:\n- Talk with you step by step like a real check-in\n- Help you quickly book a psychiatrist/counseling appointment\n\nWhich one do you want first?",
			"Meka share kala ekata thanks. Oya alone nemei.\n\nDan mama eka widihakata support karanna puluwan:\n- real check-in ekak wage thawa katha karanna\n- psychiatrist / counseling appointment ekak ඉක්මනින් book karanna help karanna\n\nKohomada issarahata yanna one?",
			$responseStyle
		);
	}

	private static function styleText($englishText, $singlishText, $responseStyle = 'default') {
		return $responseStyle === 'singlish' ? $singlishText : $englishText;
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
