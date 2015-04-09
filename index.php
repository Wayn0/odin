<?php
/**
 * index.php
 *
 * This is the entry point for the odin web application framework
 *
 * @package     Odin Framework
 * @author      Wayne Oliver <wayne@open-is.co.za>
 * @copyright   2011 Wayne Oliver <wayne@open-is.co.za> 
 * @license     BSD
 ********************************** 80 Columns *********************************
 **/

// Session Start
session_start();
session_regenerate_id();

// Define some constants
// Log current time and mem usage
define('START_TIME', microtime(true));
define('START_MEMORY_USAGE', memory_get_usage());

// Server details
if (empty($_SERVER['HTTPS'])) {
  $protocol = 'http';
}
else {
  $protocol = 'https';
}

$port = '';
if(($_SERVER['SERVER_PORT'] != 80) && ($_SERVER['SERVER_PORT'] != 443))
  $port = ':' . $_SERVER['SERVER_PORT'];

// Server information
define('SERVER', $protocol . '://' . $_SERVER['SERVER_NAME'] . $port);
define('BASE_URL', SERVER . substr($_SERVER['SCRIPT_NAME'], 0, 
		strrpos($_SERVER['SCRIPT_NAME'], "/")+1));

// Define the OS constants
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', realpath(dirname(__FILE__)));

// System CONSTANTS
define('SYS_DIR', ROOT . DS . 'system' . DS);
define('LOG_DIR', ROOT . DS . 'logs' . DS);
define('LIB_DIR', SYS_DIR . 'lib' . DS);

// Define URL constants
define('STAT_URL', BASE_URL . 'static/');
define('CSS_URL', STAT_URL . 'css/');
define('JS_URL', STAT_URL . 'js/');
define('IMG_URL', STAT_URL . 'img/');
define('IMAGES_URL', STAT_URL . 'images/');
define('ICO_URL', STAT_URL . 'ico/');

// Define application CONSTANTS
define('APP_DIR', ROOT . DS . 'app' . DS);
define('CONF_DIR', APP_DIR . 'config' . DS);
define('MOD_DIR', APP_DIR .  'modules' . DS);
define('APPLIB_DIR', APP_DIR . 'lib' . DS);
define('TEMPLATE_DIR', APP_DIR . 'templates' . DS);

// Check that the app is installed and configured
if (!file_exists(CONF_DIR . 'config.inc.php')) {
  //include 'install.php';
  //TODO: Implement install.php
  echo "ERROR: Application not correctly installed, missing config.";
  break;
}

// Check the environment before starting the application 
if ((!file_exists(LOG_DIR)) || (!is_writable(LOG_DIR)) || (phpversion() < 5.3)) {
  //include 'errors/init-error.inc.php';
  //TODO: ERROR Handling
  echo "<p>ERROR: Initializing application!</p>";
  echo "<p>check the following</p>";
  echo "<p>PHP Version: " . floor(phpversion()) . " required 5.3.x</p> ";
  echo "<p>Check that " . LOG_DIR . " exists and is writable by your web server</p>";
  break;
}
else {
  // Get the requested URL
  if (!isset($_GET['url'])) {
    $url="/";
  }
  else {
    $url = $_GET['url'];
  }
	
  // Load the config
  require_once(CONF_DIR . 'config.inc.php');
  // Start base the application
  require_once(LIB_DIR . 'init.inc.php');
}
