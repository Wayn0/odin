<?php

/**
 * @apiName Register
 * @apiGroup Authentication
 * @apiVersion 1.0.0
 * 
 * @api {POST} /api/register.json Register a user account
 * @apiParam {String} email 		User's email address as a username
 * @apiParam {String} first_name	User's name
 * @apiParam {String} last_name 	User's surname
 * 
 * @apiSuccess (200) {String} jwt Javascript web token for subsequent requests
 * 
 * @apiSuccessExample {json} Success-Response:
 * 	HTTP/1.1 200 OK
 * 	{
 * 		"tag":"registration",
 * 		"success":1,
 * 		"error":0,
 * 		"msg":"User created successfully"
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
 * 	{"tag":"login","success":0,"error":2,"error_msg":"Invalid Email address"}
 * 
 * @apiError BadUsername  Username already in use
 * @apiErrorExample {json} BadUsernamePassword:
 * 	HTTP/1.1 400 Unauthorized
 * 	{
 * 		"tag":"registration",
 * 		"success":0,
 * 		"error":3,
 * 		"error_msg":"username is already registered"
 * 	}
 * 
 * @apiError ServerError  ServerError
 * @apiErrorExample (500) {json} Server Error:
 * 	HTTP/1.1 500 Server Error
 * 	{
 * 		"tag":"registration",
 * 		"success":0,
 * 		"error":4,
 * 		"error_msg":"Could not create user: %username%"
 * 	} 
 */
  
$user = new User($params); 
header('Content-type: application/json');

if (isset($_POST['email']) 
	&& isset($_POST['first_name'])
	&& isset($_POST['last_name'])) {
	// simple sanatize of input
    $username   = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name  = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
	
	if (filter_var($username, FILTER_VALIDATE_EMAIL)) {

		// Check if user exists
		if(User::verifyEmail($params, $username)) {
			$log->logError("REGISTER: username is already registered");
			$json_data['tag'] = "registration";
			$json_data['success'] = 0;
			$json_data['error'] = 3;
			$json_data['error_msg'] = "username is already registered";
			echo json_encode($json_data);
			header("HTTP/1.1 400 Bad Request"); 
				
		} else {
			
			$user->__set('created_by_id',0);
			$user->__set('email',$username);
			$user->__set('first_name',$first_name);
			$user->__set('last_name',$last_name);
			$user->__set('auth_provider_id',1);
			
			if($user->create()) {
				
				$log->logDebug("REGISTER: created user: $username");
				$json_data['tag'] = "registration";
				$json_data['success'] = 1;
				$json_data['error'] = 0;
				$json_data['msg'] = "User created successfully";
				echo json_encode($json_data);
				header("HTTP/1.1 200 Ok"); 					
				exit;					
				
			} else {
				$log->logError("REGISTER: Internal Server Error Could not create user: $username");
				$json_data['tag'] = "registration";
				$json_data['success'] = 0;
				$json_data['error'] = 3;
				$json_data['error_msg'] = "Could not create user: $username";
				echo json_encode($json_data);
				header("HTTP/1.1 500 Server Error"); 					
				exit;	
			}
		}
		
	} else {
		$log->logError("REGISTER: Invalid email address");
		$json_data['tag'] = "registration";
		$json_data['success'] = 0;
		$json_data['error'] = 2;
		$json_data['error_msg'] = "Invalid Email address";
		echo json_encode($json_data);
		header("HTTP/1.1 400 Bad Request"); 
	}
} else {
	$log->logError("REGISTER: Invalid Request, missing parameters");
	$json_data['tag'] = "registration";
	$json_data['success'] = 0;
	$json_data['error'] = 1;
	$json_data['error_msg'] = "Invalid Request";
	echo json_encode($json_data);
	header("HTTP/1.1 400 Bad Request"); 	
}	
