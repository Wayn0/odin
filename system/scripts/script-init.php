<?php
// Variables required for all 
// Define the OS constants
define('DS', DIRECTORY_SEPARATOR);
define('SCRIPT_ROOT', realpath(dirname(__FILE__)));
define('ROOT', realpath(dirname('..' . DS . '..' . DS . 'index.php')));
// System CONSTANTS
define('SYS_DIR', ROOT . DS . 'system' . DS);
define('LOG_DIR', ROOT . DS . 'logs' . DS);
define('LIB_DIR', SYS_DIR . 'lib' . DS);
// Define application CONSTANTS
define('APP_DIR', ROOT . DS . 'app' . DS);
define('APPLIB_DIR', APP_DIR . 'lib' . DS);
define('CONF_DIR', APP_DIR . 'config' . DS);

// Check that the app is installed and configured
if (!file_exists(CONF_DIR . 'config.inc.php')) {
	die("ERROR: Application not correctly installed, missing config. please run generate-config.php");
}
// Get config
require_once(CONF_DIR . 'config.inc.php');

// Templating
define('TEMPLATE_DIR', ROOT . DS . 'static' . DS . 'templates' . DS);
define('CACHE_DIR', ROOT . DS . 'cache' . DS);

// Autoload classes from the LIB dir
spl_autoload_register(function ($class_name)  {
	if (file_exists(LIB_DIR . DS . $class_name . '.class.php')) {
		require_once(LIB_DIR . DS . $class_name . '.class.php');
	}
	if (file_exists(APPLIB_DIR . DS . $class_name . '.class.php')) {
		require_once(APPLIB_DIR . DS . $class_name . '.class.php');
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
	Util::$log = $log;
}
else {
	error_reporting(E_ALL);
	ini_set('display_errors','Off');
	$log = new Log\Log(LOG_DIR,LOG_LEVEL);
	Util::$log = $log;
}

switch (DB_TYPE) {
    case 'mysql':
        $mysql_dsn = 'mysql:dbname=' . DB_NAME .';host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8';
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

//Parameters for passing to custom classes
$params        = array();
$params['db']  = $db;
$params['log'] = $log;

// Twig for rendering
$loader = new Twig_Loader_Filesystem(TEMPLATE_DIR);
if (DEVELOPMENT_ENVIRONMENT == true) {
  $twig = new Twig_Environment($loader, array(
    'debug' => true,
  ));
  $twig->addExtension(new Twig_Extension_Debug());  
} else {
  $twig = new Twig_Environment($loader, array(
    'cache' => CACHE_DIR,
  ));  
}

$template_vars = array(
	'title'      => APP_NAME,
	'add_css'    => '',
	'add_js'     => '',
);

switch(THEME) {

	case "instacom":
		if(defined('ENV') && ENV == 'dev') {
			$template_vars['base_url'] = "https://dev.instacom.co.za/portal/";
		} else if (defined('ENV') && ENV == 'test') {
			$template_vars['base_url'] = "https://testing.instacom.co.za/";
		} else if (defined('ENV') && ENV == 'local') {
			$template_vars['base_url'] = "http://localhost/instacom/";
		} else {
			$template_vars['base_url'] = "https://portal.instacom.co.za/";
		}
		break;

	case "vodacom":
		$template_vars['base_url'] = "https://portal.ptx.vodacombusiness.co.za/";
		break;

	case "mtn":
		$template_vars['base_url'] = "https://portal-ptx.mtnbusiness.co.za/";
		break;

	default:
		$template_vars['base_url'] = "";
		break;
}
