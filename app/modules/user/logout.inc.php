<?php
// Remove the session authenticated variable
// Destroy the session

// Oauth2 Logout
unset($_SESSION['access_token']);
unset($_SESSION['token-type']);
unset($_SESSION[APP_NAME]);
session_destroy();

//$log->logDebug("LOGOUT Session information : " . print_r($_SESSION,TRUE));
header("Location: " . BASE_URL . "user/login");
//echo "logged out";
