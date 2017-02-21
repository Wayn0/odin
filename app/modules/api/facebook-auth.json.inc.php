<?php

/**
 * @apiName facebookauth
 * @apiGroup Authentication
 * @apiVersion 1.0.0
 * 
 * @api {POST} /api/facebookauth.json Authenticate user
 * @apiParam {String} email 	User's email address as a username
 * @apiParam {String} password	User's password
 * 
 * @apiSuccess (200) {String} jwt Javascript web token for subsequent requests
 * 
 * @apiSuccessExample {json} Success-Response:
 * 	HTTP/1.1 200 OK
 * 	{
 * 		"jwt":"xxxx.xxxx.xxxx"
 * 	}
 * 
 * @apiError InvalidRequest  Invalid Parameters
 * @apiErrorExample {json} InvalidRequest:
 * 	HTTP/1.1 400 Bad Request
 * 	{"tag":"facebookauth","success":0,"error":1,"error_msg":"Invalid Request"}
 *  
 * @apiError InvalidEmail  Malformed email address
 * @apiErrorExample {json} InvalidEmail:
 * 	HTTP/1.1 400 Bad Request
 * 	{"tag":"facebookauth","success":0,"error":2,"error_msg":"Invalid Email address"}
 * 
 * @apiError BadUsernamePassword  Bad username / password combinations
 * @apiErrorExample {json} BadUsernamePassword:
 * 	HTTP/1.1 401 Unauthorized
 * 	{"tag":"facebookauth","success":0,"error":3,"error_msg":"Bad username or password!"}
 * 
 * @apiError BadKey  Invalid API key
 * @apiErrorExample {json} BadKey:
 * 	HTTP/1.1 401 Unauthorized
 * 	{"tag":"facebookauth","success":0,"error":4,"error_msg":"Bad Key"}
 * 
 * @apiError ServerError  ServerError
 * @apiErrorExample {json} BadKey:
 * 	HTTP/1.1 500 Server Error
 * 	{"tag":"facebookauth","success":0,"error":4,"error_msg":"Could not create user: %username%"}
 * 
 */
  
$user = new User($params); 
header('Content-type: application/json');

if (isset($_POST['email']) 
	&& isset($_POST['api_key'])
	&& isset($_POST['fb_uid'])
	&& isset($_POST['first_name'])
	&& isset($_POST['last_name'])) {
		
    $username = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $api_key  = filter_input(INPUT_POST, 'api_key', FILTER_SANITIZE_STRING);
    $fname    = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $lname    = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    
    if ($api_key != FACEBOOK_API_KEY) {
		$log->logError("FACEBOOKAUTH: Bad Key");
		$json_data['tag'] = "facebookauth";
		$json_data['success'] = 0;
		$json_data['error'] = 4;
		$json_data['error_msg'] = "Bad Key";
		echo json_encode($json_data);
		header("HTTP/1.1 401 Unauthorized"); 		
		exit;
	}
	
	if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
		
		if (!$uid = User::verifyEmail($params,$username)) {
			$user->__set('created_by_id',0);
			$user->__set('auth_provider_id',3);
			$user->__set('email',$username);
			$user->__set('first_name',$fname);
			$user->__set('last_name',$lname);
			if (!$user->create()) {
				$log->logError("FACEBOOKAUTH: Internal Server Error Could not create user: $username");
				$json_data['tag'] = "facebookauth";
				$json_data['success'] = 0;
				$json_data['error'] = 3;
				$json_data['error_msg'] = "Could not create user: $username";
				echo json_encode($json_data);
				header("HTTP/1.1 500 Server Error"); 					
				exit;
			}
		}
		
		$log->logDebug("FACEBOOKAUTH: success: $username");
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
			
		$unencodedArray =  ['jwt' => $jwt];
		echo json_encode($unencodedArray);
		header("HTTP/1.1 200 Ok"); 	

		
	} else {
		$log->logError("FACEBOOKAUTH: Invalid Email Address");
		$json_data['tag'] = "facebookauth";
		$json_data['success'] = 0;
		$json_data['error'] = 2;
		$json_data['error_msg'] = "Invalid Email address";
		echo json_encode($json_data);
		header("HTTP/1.1 400 Bad Request"); 
	}
} else {
	$log->logError("FACEBOOKAUTH: Invalid Request, missing parameters");
	$json_data['tag'] = "facebookauth";
	$json_data['success'] = 0;
	$json_data['error'] = 1;
	$json_data['error_msg'] = "Invalid Request";
	echo json_encode($json_data);
	header("HTTP/1.1 400 Bad Request"); 	
}	



