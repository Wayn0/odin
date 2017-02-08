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
 
namespace Odin\Base;
 
class OdinUtil 
{
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
	public static function get_salt() 
	{
		$salt = base64_encode(mcrypt_create_iv(24, MCRYPT_DEV_URANDOM));
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
	public static function get_hash($password,$salt) 
	{
		// USE SHA512
		return crypt($password, '$6$rounds=10000$' . $salt);
	}	
}