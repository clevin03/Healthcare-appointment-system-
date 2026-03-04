<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../phpmailer/src/Exception.php';
require __DIR__ . '/../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/src/SMTP.php';

header('Content-Type: application/json');
ob_start();

$response = array('success' => false, 'message' => '');

function writeMailLog($status, $toEmail, $subject, $detail = '') {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $line = sprintf(
        "[%s] %s | to=%s | subject=%s | detail=%s\n",
        date('Y-m-d H:i:s'),
        $status,
        $toEmail,
        $subject,
        $detail
    );

    file_put_contents($logDir . '/mail.log', $line, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $toEmail = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($toEmail === '' || $subject === '' || $message === '') {
            $response['message'] = 'Please fill in all fields!';
            writeMailLog('FAILED_VALIDATION', $toEmail, $subject, 'Missing required fields');
            echo json_encode($response);
            exit;
        }

        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Please enter a valid email address!';
            writeMailLog('FAILED_VALIDATION', $toEmail, $subject, 'Invalid email format');
            echo json_encode($response);
            exit;
        }

        $mail = new PHPMailer(true);

        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kevinhiroshdabarera@gmail.com';
        $mail->Password = 'niqpehsbtkckdxsj';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->Timeout = 30;
        $mail->ConnectTimeout = 30;

        $mail->setFrom('kevinhiroshdabarera@gmail.com', 'BCI Healthcare Center');
        $mail->addReplyTo('kevinhiroshdabarera@gmail.com', 'BCI Healthcare Center');
        $mail->addAddress($toEmail);
        $mail->addBCC('kevinhiroshdabarera@gmail.com');

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = htmlspecialchars($subject);
        $mail->Body = nl2br(htmlspecialchars($message));
        $mail->AltBody = strip_tags($message);

        if ($mail->send()) {
            $response['success'] = true;
            $response['message'] = 'Email sent successfully!';
            writeMailLog('SENT_TO_SMTP', $toEmail, $subject, 'Accepted by SMTP server');
        } else {
            $response['message'] = 'Email failed to send. Please try again!';
            writeMailLog('FAILED_SMTP', $toEmail, $subject, $mail->ErrorInfo);
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
        writeMailLog('EXCEPTION', $toEmail ?? '', $subject ?? '', $e->getMessage());
    } catch (\Throwable $e) {
        $response['message'] = 'Server error: ' . $e->getMessage();
        writeMailLog('THROWABLE', $toEmail ?? '', $subject ?? '', $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method.';
}

ob_clean();
echo json_encode($response);
exit;
