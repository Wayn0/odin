<?php


$template_vars['feedback'] = '
                    <div class="alert alert-info alert-dismissable">
                        <!-- <button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button> -->
                        Please enter your <b>email address</b> to reset your password!
                    </div>';
 
// Reset Password form submitted
if (isset($_POST['email'])) {

	$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

	if ($email === FALSE) {
		$feedback = '<div class="alert alert-danger">Invalid email address</div>';
		$log->logError("PASSWORD RESET: Invalid email address: $email");

	} else if (!User::verifyEmail($params,$email)){
		// User does not exists
		$log->logError("PASSWORD RESET: $email does not exist");
		$template_vars['feedback'] = '
					  <div class="alert alert-danger alert-dismissable">
						  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
						  <b>Error!</b> email address does not exist
					  </div>';
	} else {
		$log->logDebug("PASSWORD RESET: $email does exist");
		$user = new User($params,'','',$email);
		if($user->resetPassword()) {
		  $log->logDebug("PASSWORD RESET: Successful: $email");
		  $template_vars['feedback'] = '
						<div class="alert alert-success alert-dismissable">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
							A new password has been emailed to you.
						</div>';
		} else {
			$log->logError("PASSWORD RESET ERROR: $email");
			$template_vars['feedback'] = '
						<div class="alert alert-danger alert-dismissable">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
							<b>Error!</b> could not reset your password.
						</div>';
		}
	}
}

echo $twig->render('user/forgot-password.twig', $template_vars);
