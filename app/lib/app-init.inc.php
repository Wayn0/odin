<?php
/**
 * app-init.inc.php
 *
 * This is the application specific bootstrapping file.
 * All custom bootstrapping goes here.
 *
 * @package     Odin Framework
 * @author      Wayne Oliver <wayne@open-is.co.za>
 * @copyright   Wayne Oliver <wayne@open-is.co.za> 2011 - 2017
 * @license     http://www.opensource.org/licenses/BSD-2-Clause
 *
 **/

// Create the user object
$session_user = new User($params);

// check if the user is logged in and load all the bits and pieces
if(isset($_SESSION[APP_NAME]['EMAIL'])) {

	// Verify session
	$session_username = $_SESSION[APP_NAME]['EMAIL'];
	$session_authenticated = $session_user->isLoggedIn($session_username);
  
} else {
	$session_username      = null;
	$session_authenticated = false;
}
