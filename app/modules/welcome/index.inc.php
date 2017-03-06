<?php

//$user = new User($params,1);
//$user->resetPassword();

echo "Welcome to Odin";

if($session_authenticated) {
	echo '<p>Logged in</p> - <a href="'. BASE_URL .'user/logout">Log out!</a>';
} else {
	echo '<p>NOT Logged in</p> - <a href="'. BASE_URL .'user/login">Login!</a>';
}
