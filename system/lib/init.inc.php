<?php
/**
 * init.inc.php
 *
 * This is the initialization file for the Odin framework
 * it handles routing and dynamic loading of classes
 *
 * @package     Odin Framework
 * @author      Wayne Oliver <wayne@open-is.co.za>
 * @copyright   Wayne Oliver <wayne@open-is.co.za> 2011 - 2017
 * @license     http://www.opensource.org/licenses/BSD-2-Clause
 *
 **/

// Autoload classes from the LIB dir
spl_autoload_register(function ($class_name) {
	
	$path = str_replace('\\', DS, $class_name);
	
	if (file_exists(LIB_DIR . $path . '.class.php')) {
		require_once(LIB_DIR . $path . '.class.php');
	}
	if (file_exists(APPLIB_DIR . $path . '.class.php')) {
		require_once(APPLIB_DIR . $path . '.class.php');
	}
});

// Composer auto loading
if (file_exists(ROOT . DS . 'vendor'. DS .'autoload.php')) {
	require_once ROOT . DS . 'vendor'. DS .'autoload.php';
} 

// Configure the app for prod/dev
if (DEVELOPMENT_ENVIRONMENT == true) {
	error_reporting(E_ALL);
	ini_set('display_errors','On');
	$log = new Log\Log(LOG_DIR,Log\Log::QUERY);
}
else {
	error_reporting(E_ALL);
	ini_set('display_errors','Off');
	$log = new Log\Log(LOG_DIR,LOG_LEVEL);
}

// Log user out if session expired.
// Below creating the log to log the event
if (isset($_SESSION[APP_NAME]['LAST_ACTIVITY']) 
     && (time() - $_SESSION[APP_NAME]['LAST_ACTIVITY'] > 60 * SESSION_TIMEOUT)) {
	($_SESSION[APP_NAME]['EMAIL']) ? $email = $_SESSION[APP_NAME]['EMAIL'] : $email ="";
	$log->logInfo("User session timeout, destroying session for user: $email");
	session_unset();     // unset $_SESSION variable for the run-time 
	session_destroy();   // destroy session data in storage
	header('Location: ' . BASE_URL . $url); // back to login page
}
// Update LAST_ACTIVITY
$_SESSION[APP_NAME]['LAST_ACTIVITY'] = time();


// Connect to the database
// Only load the DB connection if required
if (DB_REQUIRED == true) {
	// Attempt to connect to the defined data
	try {
		switch (DB_TYPE) {
			case 'mysql':
				$mysql_dsn = 'mysql:dbname=' . DB_NAME .';host=' . DB_HOST . ';port=' . DB_PORT;
				$db = new PDO($mysql_dsn, DB_USER, DB_PASS);
				break;

			case 'sqlite':
				$sqlite_dsn = 'sqlite:' . LOG_DIR . DS . DB_NAME . '.sqlite' ;
				$db = new PDO($sqlite_dsn);
				break;

			case 'pgsql':
				$pgsql_dsn = 'pgsql:host='.DB_HOST.';dbname='.DB_NAME.';user='.DB_USER.';password='.DB_PASS.';';
				$db = new PDO($pgsql_dsn );
				break;

			default:
				throw new Exception("Invalid database type: " . DB_TYPE);
		}

		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	} catch (PDOException $e) {
		$log->logFatal("Database connection error: check config.inc.php and that database is configured and running");
		die('<strong>Could not connect to the database: </strong><p>Please check that your configured database server is running...</p>');
	}
} else {
	$db = null;
}

//Parameters for passing to custom classes
$params        = array();
$params['db']  = $db;
$params['log'] = $log;

// Twig for rendering
$loader = new Twig_Loader_Filesystem(TEMPLATE_DIR);
if (DEVELOPMENT_ENVIRONMENT == true) {
	$twig = new Twig_Environment($loader, array('debug' => true));
	$twig->addExtension(new Twig_Extension_Debug());  
} else {
	$twig = new Twig_Environment($loader, array('cache' => CACHE_DIR,));  
}

$template_vars = array(
	'title'      => APP_NAME,
	'base_url'   => BASE_URL,
	'static_url' => STAT_URL,
	'css_url'    => CSS_URL,
	'js_url'     => JS_URL,
	'img_url'    => IMG_URL,
	'images_url' => IMAGES_URL,  
	'asset_url'  => ASSET_URL,
	'ico_url'    => ICO_URL,
	'add_css'    => '',
	'add_js'     => '',
);

// Make request headers available to modules
if(function_exists('apache_request_headers')) {
       $request_headers = apache_request_headers();
} else {
       $request_headers = array();
}

// Begin Routing section
// Expand the url into an array for easy manipulation
$url_array = array();
$url_array = explode("/",$url);

// Split the various parts of the url into there module/action/get-variables
$module = $url_array[0];
if ($module == '') {
	// If no module has been specified then use default home
	$module = DEFAULT_MODULE;
	$action = DEFAULT_ACTION;
	$query_string = array();
}
else {
	//Check if an action has been specified, if not assign the default action
	array_shift($url_array);
	if (sizeof($url_array) == 0) {
		$action = DEFAULT_ACTION;
	}
	else {
		$action = $url_array[0];
		if ($action == '')
			$action = DEFAULT_ACTION;
	}

	array_shift($url_array);
	$query_string = $url_array;
}

// Check the module that's being called and that it exists
if (!file_exists(MOD_DIR . strtolower($module))) {
	$log->logError("Invalid module: $module action: $action");
	$module          = 'error';
	$action          = 'index';
	$query_string[0] = 404;
}
// Check the action exists if the default is not specified
elseif(!file_exists(MOD_DIR . strtolower($module) . DS . strtolower($action) . '.inc.php')) {
	$log->logError("Invalid module: $module action: $action");
	$module          = 'error';
	$action          = 'index';
	$query_string[0] = 404;
}

// DEBUG INFORMATION
$request_method  = $_SERVER['REQUEST_METHOD'];
$request_time    = time() - $_SERVER['REQUEST_TIME'];
$method_string   = '_' . $request_method;
$request_details = "";

if($request_method == "GET" || $request_method == "POST") {

	$method_array    = $$method_string;
	if (array_key_exists("password",$method_array))
	  $method_array["password"]= "**************";
	if (array_key_exists("password1",$method_array))
	  $method_array["password1"]= "**************";
	if (array_key_exists("password2",$method_array))
	  $method_array["password2"]= "**************";
	if(!empty($method_array)) {
		$request_details = print_r($method_array,true);
	} else {
		$request_details = "";
	}
}

// Display any uploads if passed
if(isset($_FILES) && !empty($_FILES))
	$files = "Files: " . print_r($_FILES,true);
else
	$files = "";
	
  
 $ip = getenv('HTTP_CLIENT_IP')?:
getenv('HTTP_X_FORWARDED_FOR')?:
getenv('HTTP_X_FORWARDED')?:
getenv('HTTP_FORWARDED_FOR')?:
getenv('HTTP_FORWARDED')?:
getenv('REMOTE_ADDR');

// Call the requested page an log the call
$log->logDebug("$ip $request_method - $url - $request_time: $request_details $files");

// Load application specific init file before including the modules
if (file_exists(APPLIB_DIR . 'app-init.inc.php')) {
	require_once(APPLIB_DIR . 'app-init.inc.php');
}  
require_once(MOD_DIR . strtolower($module) . DS . strtolower($action) . '.inc.php');
$log->logDebug("Memory used by request: " . Util::bytes_size((memory_get_usage() - START_MEMORY_USAGE)) . " Time: " . round((microtime(true) - START_TIME),2) . "s");
