<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Twilio\Rest\Client;

class WhatsAppService {
    private $client;
    private $twilio_phone;
    private $account_sid;
    private $auth_token;
    
    public function __construct() {
        $this->account_sid = getenv('TWILIO_ACCOUNT_SID') ?: 'ACCOUNT_SID';
        $this->auth_token = getenv('TWILIO_AUTH_TOKEN') ?: 'AUTH_TOKEN';
        $this->twilio_phone = getenv('TWILIO_WHATSAPP_NUMBER') ?: '+14155238886';
        
        if ($this->account_sid !== 'ACCOUNT_SID' && $this->auth_token !== 'AUTH_TOKEN') {
            $this->client = new Client($this->account_sid, $this->auth_token);
        }
    }
    
    public function sendMessage($phone, $message) {
        if (!$this->client) {
            return ['success' => false, 'error' => 'Twilio not configured'];
        }
        
        try {
            if (!preg_match('/^\+\d{10,15}$/', $phone)) {
                return ['success' => false, 'error' => 'Invalid phone number format'];
            }
            
            $msg = $this->client->messages->create(
                'whatsapp:' . $phone,
                array(
                    'from' => $this->twilio_phone,
                    'body' => $message
                )
            );
            
            return ['success' => true, 'message_id' => $msg->sid];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function sendAppointmentReminder($phone, $patient_name, $doctor_name, $date, $time) {
        $message = "Hi $patient_name,\n\nThis is a reminder of your appointment with Dr. $doctor_name on $date at $time.\n\nPlease arrive 10 minutes early.\n\nThank you!";
        return $this->sendMessage($phone, $message);
    }
    
    public function sendAppointmentConfirmation($phone, $appointment_number, $date, $time) {
        $message = "Your appointment #$appointment_number is confirmed for $date at $time.\n\nReply CANCEL to cancel or RESCHEDULE to request a new time.";
        return $this->sendMessage($phone, $message);
    }
    
    public function sendAppointmentCancellation($phone, $date) {
        $message = "Your appointment on $date has been cancelled.\n\nIf you wish to reschedule, please contact us.\n\nWe regret any inconvenience.";
        return $this->sendMessage($phone, $message);
    }
    
    public function sendAppointmentRescheduled($phone, $new_date, $new_time, $old_date) {
        $message = "Your appointment has been rescheduled to $new_date at $new_time.\n\nYour previous appointment on $old_date has been cancelled.";
        return $this->sendMessage($phone, $message);
    }
    
    public function isConfigured() {
        return $this->client !== null && $this->account_sid !== 'ACCOUNT_SID' && $this->auth_token !== 'AUTH_TOKEN';
    }
}

?>
