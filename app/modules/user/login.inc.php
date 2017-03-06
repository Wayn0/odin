<?php
/**
 * user/login.inc.php
 *
 * This the login handler
 *
 * @package     Odin Framework
 * @author      Wayne Oliver <wayne@open-is.co.za>
 * @copyright   Wayne Oliver <wayne@open-is.co.za> 2011 - 2017
 * @license     http://www.opensource.org/licenses/BSD-2-Clause
 *
 **/
  
$session_user = new User($params); 
 
// Check if the form has been submitted
$template_vars['feedback'] = '';   
if (isset($_POST['email'])) {
	// Get form contents
	$username = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
	$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_EMAIL);

	$session_user->__set('email',$username);
	// Check username and password
	if($session_user->verifyPassword($password)) {
		$log->logInfo("LOGIN: success: $username");

		$tokenId    = base64_encode(random_bytes(32));
		$issuedAt   = time();
		$serverName = BASE_URL;			

		/*
		* Create the token as an array
		*/
		$data = [
			'iat'  => $issuedAt,         // Issued at: time when the token was generated
			'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
			'iss'  => $serverName,       // Issuer
			'nbf'  => $issuedAt,         // Not before
			'exp'  => $issuedAt + (60 * SESSION_TIMEOUT), // Expire in predefined mins
			'data' => [                  // returned data
				'user_id' => $session_user->__get('id'),
			]
		];			


		$secret = base64_decode(JWT_SECRET);


		$jwt = \Firebase\JWT\JWT::encode(
			$data,   //Data to be encoded in the JWT
			$secret, // The signing key
			'HS512'  // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
		);   

		$_SESSION[APP_NAME]['JWT'] = $jwt;

		if($session_user->__get('change_password')) {
			$log->logDebug("Password change required for " . $username);
			$session_user->redirect('user/change-password');
		}

		if ((isset($query_string[0])) && ($query_string[0] == 'referer')) {
			array_shift($query_string);
			$path = implode("/", $query_string);
			$log->logDebug("Referer set, redirecting to: " . $path);
			$session_user->redirect($path);
		} else {
			$_SESSION[APP_NAME]['LAST_ACTIVITY'] = time();
			$log->logDebug("Referer not set, redirecting to: " . BASE_URL);
			$session_user->redirect('');
		}
	} else {
		$template_vars['feedback'] = '
<div class="alert alert-danger" role="alert">
  <strong>Error!</strong> Invalid Login
</div>		
		';
	}
	
} else {
	// Handle referer for alternate logins
	if ((isset($query_string[0])) && ($query_string[0] == 'referer')) {
		array_shift($query_string);
		$path = implode("/", $query_string);
		$template_vars['referer'] = "?referer=$path";
	}  
}



echo $twig->render('user/login.twig', $template_vars);
