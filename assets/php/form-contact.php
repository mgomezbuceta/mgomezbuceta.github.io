<?php
header("Content-Type: application/json;charset=utf-8");
$errorMSG = '';

define("RECAPTCHA_V3_SECRET_KEY", '6LcrTJ0aAAAAAOPHTepGCTq1jbHixLLDWDgiDcVq');

require_once('phpmailer/src/Exception.php');
require_once('phpmailer/src/PHPMailer.php');
require_once('phpmailer/src/SMTP.php');

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// processing of data sent only by the POST method (for the remaining methods we complete the execution of the script)
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	echo json_encode(['success' => 'false', 'error' => 'Wrong method']);
	exit();
}

if (isset($_POST['recaptcha'])) {
	$captcha = $_POST['recaptcha'];
} else {
	$captcha = false;
}

if (!$captcha) {
	echo json_encode(['success' => 'false', 'error' => 'Failed submit form']);
	exit();
} else {
	$url = 'https://www.google.com/recaptcha/api/siteverify';
	$data = ['secret' => RECAPTCHA_V3_SECRET_KEY, 'response' => $captcha];

	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data)
		)
	);

	$context  = stream_context_create($options);
	$response = file_get_contents($url, false, $context);
	$responseKeys = json_decode($response, true);

	var_dump($responseKeys);

	if ($responseKeys->success === false) {
		echo json_encode(['success' => 'false', 'error' => 'Failed submit form']);
		exit();
	}
}

//... The Captcha is valid you can continue with the rest of your code
//... Add code to filter access using $response . score
if ($responseKeys->success == 1 && $responseKeys->score <= 0.5) {
	echo json_encode(['success' => 'false', 'error' => 'Failed submit form']);
	exit();
}

// NAME
if (isset($_POST['name'])) {
	$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
} else {
	$errorMSG = 'Name is required';
}

// EMAIL
if (isset($_POST['email'])) {
	if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	} else {
		$email = $_POST['email'];
	}
} else {
	$errorMSG .= 'Email is required';
}

// MESSAGE
if (isset($_POST['message'])) {
	$message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
} else {
	$errorMSG .= 'Message is required';
}

// Curren year
$current_year = date('Y');

// email template
$body = "
<html>
<body>
	<div style='background:#f9f9f9; padding:1px;'>
		<div style='text-align: center; margin-top: 20px;'><h2>MGB</h2></div>
		<div style='background:#fff; width:600px; margin:20px auto; padding:35px 60px 25px; box-sizing:border-box; border-radius:4px; box-shadow: 0 15px 40px rgba(141, 153, 167, 0.05);'>
			<div style='color:#8d99a7;'>Name:</div>
			<div style='margin-bottom:10px;'>$name</div>
			<div style='color:#8d99a7;padding-top:13px;border-top:1px solid #f3f5f6;'>Email sender:</div>
			<div style='margin-bottom:10px;'>$email</div>
			<div style='color:#8d99a7;padding-top:13px;border-top:1px solid #f3f5f6;'>Comment:</div>
			<div style='margin-bottom:10px;'>$message</div>
		</div>
		<div style='color:#8d99a7; font-size:12px; text-align:center; padding-bottom:20px;'>Copyright $current_year vCard</div>
	</div>
</body>
</html>
";

//send email using PHPMailer
$mail = new PHPMailer(true);

//Server settings
$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
$mail->isSMTP();                                            //Send using SMTP
$mail->Host       = 'mgomezbuceta-com.correoseguro.dinaserver.com';                     //Set the SMTP server to send through
$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
$mail->Username   = 'info@mgomezbuceta.com';                     //SMTP username
$mail->Password   = 'Mgb110279';                               //SMTP password
//$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
$mail->Port       = 465;

//Recipients
$mail->setFrom('no-reply@mgomezbuceta.com', 'MGB');
$mail->addAddress('info@mgomezbuceta.com', 'MGB');

// Content
$mail->CharSet = 'UTF-8';
$mail->IsHTML(true);
$mail->Subject = 'Contact Form';  // subject of the letter
$mail->Body = $body;
$mail->AltBody = 'Name: $name, Email sender: $email, Comment: $message';

if ($mail->send() && $errorMSG == '') {
	echo json_encode(['success' => 'true', 'error' => '']);
	exit();
} else {
	if ($errorMSG == '') {
		echo json_encode(['success' => 'false', 'error' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
		exit();
	} else {
		echo json_encode(['success' => 'false', 'error' => $errorMSG]);
		exit();
	}
}