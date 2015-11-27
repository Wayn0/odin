<?php
/**
 * init.inc.php
 *
 * This is the initialization file for the Odin framework
 * it handles routing and dynamic loading of classes
 *
 * @package     Odin Framework
 * @author      Wayne Oliver <wayne@open-is.co.za>
 * @copyright   Wayne Oliver <wayne@open-is.co.za> 2011 - 2015
 * @license     BSD
 ********************************** 80 Columns *********************************
 **/

// Autoload classes from the LIB dir
spl_autoload_register(function ($class_name)  {
  if (file_exists(LIB_DIR . DS . $class_name . '.class.php')) {
    require_once(LIB_DIR . DS . $class_name . '.class.php');
  }
  if (file_exists(APPLIB_DIR . DS . $class_name . '.class.php')) {
    require_once(APPLIB_DIR . DS . $class_name . '.class.php');
  }
});

// composer bits
require_once(ROOT . DS . 'vendor'. DS .'autoload.php');


// Configure the app for prod/dev
if (DEVELOPMENT_ENVIRONMENT == true) {
  error_reporting(E_ALL);
  ini_set('display_errors','On');
  $log = new Log(LOG_DIR,Log::QUERY);
}
else {
  error_reporting(E_ALL);
  ini_set('display_errors','Off');
  $log = new Log(LOG_DIR,LOG_LEVEL);
}

// Connect to the database
// Only load the DB connection if required
if (DB_REQUIRED == true) {
  // Attempt to connect to the defined data
  try
  {
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
  }

  catch (PDOException $e)
  {
    $log->log_fatal("Database connection error: check config.inc.php and that database is configured and running");
    die('<strong>Could not connect to the database: </strong><p>Please check that your configured database server is running...</p>');
  }
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

// Load application specific init file before including the modules
if (file_exists(APPLIB_DIR . 'app-init.inc.php')) {
  require_once(APPLIB_DIR . 'app-init.inc.php');
}

// Check the module that's being called and that it exists
if (!file_exists(MOD_DIR . strtolower($module))) {
  $log->log_error("Invalid module: $module action: $action");
  $module          = 'error';
  $action          = 'index';
  $query_string[0] = 404;
}
// Check the action exists if the default is not specified
elseif(!file_exists(MOD_DIR . strtolower($module) . DS . strtolower($action) . '.inc.php')) {
  $log->log_error("Invalid module: $module action: $action");
  $module          = 'error';
  $action          = 'index';
  $query_string[0] = 404;
}

// Call the requested page an log the call
$log->log_debug("Calling: $module/$action with variables: " . implode(",", $query_string));
require_once(MOD_DIR . strtolower($module) . DS . strtolower($action) . '.inc.php');

