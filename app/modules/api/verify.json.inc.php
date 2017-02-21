<?php

/**
 * @apiName Verify
 * @apiGroup Authentication
 * @apiVersion 1.0.0
 * 
 * @api {POST} /api/verify.json		Check if user is registerd
 * @apiParam {String} email 		User's email address as a username

 * 
 * @apiSuccess (200) {String} jwt Javascript web token for subsequent requests
 * 
 * @apiSuccessExample {json} Success-Response:
 * 	HTTP/1.1 200 OK
 * 	{
 * 		"tag":"verify",
 * 		"success":1,
 * 		"error":0,
 * 		"msg":"User exists"
 * 	}
 *  
 * @apiError InvalidEmail	Invalid or missing email address
 * @apiErrorExample {json} InvalidEmail:
 * 	HTTP/1.1 400 Bad Request
 * 	{
 * 		"tag":"login",
 * 		"success":0,
 * 		"error":1,
 * 		"error_msg":"Invalid Email address"
 * 	}
 * 
 */
  
$user = new User($params); 
header('Content-type: application/json');

if (isset($_POST['email'])) {
	// simple sanatize of input
    $username = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
	
	if (filter_var($username, FILTER_VALIDATE_EMAIL)) {

		// Check if user exists
		if(User::verifyEmail($params, $username)) {
			
			$log->logDebug("VERIFY: username is registered");
			$json_data['tag'] = "verify";
			$json_data['success'] = true;
			$json_data['error'] = 0;
			$json_data['msg'] = "username is registered";
			echo json_encode($json_data);
			header("HTTP/1.1 200 Ok"); 
				
		} else {
			
			$log->logDebug("VERIFY: username is NOT registered");
			$json_data['tag'] = "verify";
			$json_data['success'] = false;
			$json_data['error_msg'] = "username is NOT registered";
			echo json_encode($json_data);
			header("HTTP/1.1 404 Not Found"); 		
		}
		
	} else {
		
		$log->logError("VERIFY: Invalid email address");
		$json_data['tag'] = "verify";
		$json_data['success'] = 0;
		$json_data['error'] = 2;
		$json_data['error_msg'] = "Invalid Email address";
		echo json_encode($json_data);
		header("HTTP/1.1 400 Bad Request"); 
	}
} else {
	
	$log->logError("VERIFY: Invalid email address");
	$json_data['tag'] = "verify";
	$json_data['success'] = 0;
	$json_data['error'] = 2;
	$json_data['error_msg'] = "Invalid Email address";
	echo json_encode($json_data);
	header("HTTP/1.1 400 Bad Request"); 
}	
