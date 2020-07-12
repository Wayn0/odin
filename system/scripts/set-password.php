<?php

require_once 'script-init.php';

echo "\nWhat's the email address \n";
$handle = fopen ("php://stdin","r");
$email = trim(fgets($handle));
echo "\nWhat's the password? \n";
$pass  = trim(fgets($handle));

$user = new User($params);
$user->email = $email;
$user->getDetailsByEmail();
print_r($user);
if(is_numeric($user->id) && $user->id > 0) {
	if($user->set_password($pass))
		echo "\nPassword for $email set to: $pass!\n\n";
	else
		echo "\nERROR!!\n\n";
} else {
	echo "\nInvalid user!\n\n";
}
