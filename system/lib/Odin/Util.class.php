<?php
/**
 * Util class, basic utilities
 * 
 * @package     Odin Framework
 * @author      Wayne Oliver <wayne@open-is.co.za>
 * @copyright   Wayne Oliver <wayne@open-is.co.za> 2011 - 2017
 * @license     http://www.opensource.org/licenses/BSD-2-Clause
 *
 **/
 
namespace Odin;
 
class Util 
{
	/**
	 * A database connection tha is responsible for all data 
	 * persistence and retrieval.
	 * @var PDO
	 */  
	public static $db = null;
	
	/**
	 * The logger that handles writing logs to file.
	 * @var Log
	 */  
	public static $log = null;	
	
	/*
	 * Create a URL safe slug from a string of text
	 *
	 * @param string $text The text you wish to slugify
	 * 
	 * @return string 
	 */	
	public static function slugify($text) 
	{ 
		// replace non letter or digits by -
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text);

		// trim
		$text = trim($text, '-');

		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// lowercase
		$text = strtolower($text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		if (empty($text)) {
			return 'n-a';
		}

		return $text;
	}
	
	/*
	 * Random String Generator
	 *
	 * @param int $length Length of string to generate
	 * @param bool $special	Include special charachters in string
	 * 
	 * @return string 
	 */
	public static function getRandomString($length=8,$special=TRUE) {
		// Characters to include in the returned string 
		$string = "abcdefghijklmnopqrstuvwxyz";
		$string = $string . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$string = $string . '1234567890';

		if ($special) {
			$string = $string . '!@#$%^-';
		}
			
		// Initialize an empty string
		$return_string = "";
		
		// Build the string
		for ($i=0; $i < $length; $i++) {
			$return_string .= $string[mt_rand(0, strlen($string)-1)];
		}
		// Return the generated string
		return $return_string;			
	}
	
	/*
	 * Salt Generator
	 *
	 * @param none
	 *
	 * @return string A password salt
	 */ 	
	public static function getSalt() 
	{
		$salt = base64_encode(random_bytes(24));
		return $salt;
	}
	
	/**
	 * Hash Generator 
	 *
	 * @param string $password Password
	 * @param string $salt SAlt
	 *
	 * @return string $hash
	 **/ 	
	public static function getHash($password,$salt) 
	{
		// USE SHA512
		return crypt($password, '$6$rounds=10000$' . $salt);
	}

	
	/**
	 * Send an email using an html template 
	 *
	 * @param string $template  Name of the *twig* template
	 * @param string $subject   Subject line for new mail
	 * @param string $variables Array of variables to be replaced in the template
	 * @param string $text_version Plain text version of the email
	 *
	 * @return bool
	 **/ 	
	public static function sendTemplateMail($template,$subject,$to,
									$variables,$text_version='')
	{
		$template_path = TEMPLATE_DIR . DS . $template;
		
		if (! file_exists($template_path)) {
			if (self::$log != null)
				self::$log->logError("$template_path does not exists!");
			return false;
		}
		
		if (defined('EMAIL_FROM')) {
			$from = EMAIL_FROM;
		} else {
			$from = 'no-reply@noreply.com';
		}
		
		// Read the template as a string  
		$fh = fopen($template_path, 'r');
		$html_message = fread($fh, filesize($template_path));
		fclose($fh);
		
		// Loop through variables replacing variables in the template
		foreach ($variables as $key => $val) {
			$html_message = str_replace('{{' . strtoupper($key) . '}}', $val, $html_message);
		}
		
		$mail = new \PHPMailer;
		$mail->isSendmail();
		$mail->addAddress($to);
		$mail->setFrom($from, APP_NAME);
		$mail->isHTML(true); 
		$mail->Subject = $subject;
		$mail->Body    = $html_message;
		$mail->AltBody = $text_version;

		$send = $mail->send();
		if ($send) {
			return true;
		} else {
			if (self::$log != null)
				self::$log->logError("EMAIL: Could not send email: " . $mail->ErrorInfo);
			return false;
		}
	}
	
	/**
	 * Send an email using an html template 
	 *
	 * @param string $template Name of the *twig* template
	 * @param string $subject Subject line for email
	 *
	 * @return bool
	 **/ 	
	public static function sendTextMail($text,$subject)
	{			
		$mail = new PHPMailer;
		$mail->isSendmail();
		$mail->setFrom($from, APP_NAME);
		$mail->addAddress($email); 
		$mail->isHTML(false); 
		$mail->Subject = $subject;
		$mail->Body    = $text;

		$send = $mail->send();
		if ($send) {
			return true;
		} else {
			if (self::$log != null)
				self::$log->logError("EMAIL: Could not send email: " . $mail->ErrorInfo);
			return false;
		}
	}
	
	/**
	 * Convert a byte size to a human readable form 
	 *
	 * @param int $size Number of bytes
	 *
	 * @return string $bytes
	 **/ 		
	public static function bytes_size($size)
	{
		$unit = array('b','kb','mb','gb','tb','pb');
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}	
}
