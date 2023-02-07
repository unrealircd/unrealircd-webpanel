<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class php_mailer
{
	public $name = "PHPMailer()";
	public $author = "Valware";
	public $version = "1.0";
	public $description = "Send mail using PHPMailer()";

	function __construct()
	{
		Hook::func(HOOKTYPE_USER_LOGIN, 'php_mailer::user_login_notif');
		Hook::func(HOOKTYPE_USER_LOGIN_FAIL, 'php_mailer::user_login_fail_notif');
	}

	public static function send_mail($to, $subject, $body)
	{
		$mail = new PHPMailer(true);
		try {
			//Server settings
			//$mail->SMTPDebug = 2;
			$mail->isSMTP();                                            //Send using SMTP
			$mail->Host       = EMAIL_SETTINGS['host'];                     //Set the SMTP server to send through
			$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
			$mail->Username   = EMAIL_SETTINGS['username'];                     //SMTP username
			$mail->Password = EMAIL_SETTINGS['password'];                               //SMTP password
			$mail->SMTPSecure = EMAIL_SETTINGS['encryption'];            //Enable implicit TLS encryption
			$mail->Port       = EMAIL_SETTINGS['port'];                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

			//Recipients
			$mail->setFrom(EMAIL_SETTINGS['username'], EMAIL_SETTINGS['from_name']);
			$mail->addAddress($to['email'], $to['name']);     //Add a recipient

			//Content
			$mail->isHTML(true);                                  //Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body    = $body . "<br><br>Thank you for using UnrealIRCd!";
			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			$mail->send();
			echo 'Message has been sent';
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}
	}

	/**
	 *  Send a login notification to the admin (note-to-self)
	 * @param mixed $user
	 * @return void
	 */
	public static function user_login_notif($user)
	{
		self::send_mail(
			["email" => EMAIL_SETTINGS['username'], "name" => EMAIL_SETTINGS['from_name']],
			"New login to Unreal Admin Panel",
			"There was a new login to the admin panel.<br>User: \"$user->username\"<br>IP: \"".$_SERVER['REMOTE_ADDR']."\""
		);
	}
	public static function user_login_fail_notif($fail)
	{
		self::send_mail(
			["email" => EMAIL_SETTINGS['username'], "name" => EMAIL_SETTINGS['from_name']],
			"Failed login attempt - Unreal Admin Panel",
			"There was a failed login attempt to the admin panel.<br>User: \"".$fail['login']."\"<br>IP: \"".$fail['IP']."\""
		);
	}
}