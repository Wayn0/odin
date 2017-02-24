<?php

/**
 * @apiName Login
 * @apiGroup Authentication
 * @apiVersion 1.0.0
 * 
 * @api {POST} /api/login.json Authenticate user
 * @apiParam {String} email 	User's email address as a username
 * @apiParam {String} password	User's password
 * 
 * @apiSuccess (200) {String} jwt Javascript web token for subsequent requests
 * 
 * @apiSuccessExample {json} Success-Response:
 * 	HTTP/1.1 200 OK
 * 	{
 * 		"tag": "login",
 * 		"success":1,
 * 		"error":0,
 * 		"first_name":"first name",
 * 		"last_name":"last name",
 * 		"jwt":"xxxx.xxxx.xxxx"
 * 	}
 * 
 * @apiError InvalidRequest  Invalid Parameters
 * @apiErrorExample {json} InvalidRequest:
 * 	HTTP/1.1 400 Bad Request
 * 	{"tag":"login","success":0,"error":1,"error_msg":"Invalid Request"}
 *  
 * @apiError InvalidEmail  Malformed email address
 * @apiErrorExample {json} InvalidEmail:
 * 	HTTP/1.1 400 Bad Request
 * 	{
 * 		"tag":"login",
 * 		"success":0,
 * 		"error":2,
 * 		"error_msg":"Invalid Email address"
 * 	}
 * 
 * @apiError BadUsernamePassword  Bad username / password combinations
 * @apiErrorExample {json} BadUsernamePassword:
 * 	HTTP/1.1 401 Unauthorized
 * 	{
 * 		"tag":"login",
 * 		"success":0,
 * 		"error":3,
 * 		"error_msg":"Bad username or password!"
 * 	}
 * 
 */
  
$user = new User($params); 
header('Content-type: application/json');

if (isset($_POST['email'])) {
	// simple sanatize of input
    $username = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
	
	if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
		// Valid email being checking
		$log->logDebug("LOGIN JSON: attempt: $username");
		if($user->verifyPassword($username, $password)) {
			$log->logInfo("LOGIN JSON: success: $username");
			$user->setLastLogin();
			
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
					'user_id'       => $user->__get('id'),
					'created_date'  => $user->__get('created_date'),
				]
			];			
			
			
			$secret = base64_decode(JWT_SECRET);
					
					
			$jwt = \Firebase\JWT\JWT::encode(
				$data,   //Data to be encoded in the JWT
				$secret, // The signing key
				'HS512'  // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
				);
				
			$unencodedArray =  ['jwt' => $jwt,
								'tag' => "login",
								'success' => 1,
								'error' => 0,
								'first_name' => $user->__get('first_name'),
								'last_name' => $user->__get('last_name')
								];
			echo json_encode($unencodedArray);
			header("HTTP/1.1 200 Ok"); 
				
		} else {
			$log->logError("LOGIN: Bad username or password");
			$json_data['tag'] = "login";
			$json_data['success'] = 0;
			$json_data['error'] = 3;
			$json_data['error_msg'] = "Bad username or password!";
			echo json_encode($json_data);
			header("HTTP/1.1 401 Unauthorized"); 			
		}
		
	} else {
		$log->logError("LOGIN: Invalid email address");
		$json_data['tag'] = "login";
		$json_data['success'] = 0;
		$json_data['error'] = 2;
		$json_data['error_msg'] = "Invalid Email address";
		echo json_encode($json_data);
		header("HTTP/1.1 400 Bad Request"); 
	}
} else {
	$log->logError("LOGIN: Invalid Request, missing parameters");
	$json_data['tag'] = "login";
	$json_data['success'] = 0;
	$json_data['error'] = 1;
	$json_data['error_msg'] = "Invalid Request";
	echo json_encode($json_data);
	header("HTTP/1.1 400 Bad Request"); 	
}	
