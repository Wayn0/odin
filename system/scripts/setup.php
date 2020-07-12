<?php

require_once 'script-functions.php';


// Define the OS constants
define('DS', DIRECTORY_SEPARATOR);
define('SCRIPTS', realpath(dirname(__FILE__)));
define('ROOT', realpath(SCRIPTS . DS . '..' . DS . '..' . DS));
define('SYS_DIR', ROOT . DS . 'system' . DS);
define('LIB_DIR', SYS_DIR . 'lib' . DS . 'Odin' . DS);
define('APP_DIR', ROOT . DS . 'app' . DS);
define('CONF_DIR', APP_DIR . 'config' . DS);
define('CONF_FILE', CONF_DIR . 'config.inc.php');
define('VENDOR', ROOT . DS . 'vendor');


// Check that composer has been run
if (file_exists(CONF_FILE))
    die("\nERROR: Config already exists, this is for new setups only\n");
    
// Check that composer has been run
if (!file_exists(VENDOR))
	die("\nERROR: Please make sure composer install has already been run\n");

// Composer auto loading
if (file_exists(ROOT . DS . 'vendor'. DS .'autoload.php')) {
	require_once ROOT . DS . 'vendor'. DS .'autoload.php';
}
require_once(LIB_DIR . 'Util.class.php');

$loader = new Twig_Loader_Filesystem(CONF_DIR);
$twig = new Twig_Environment($loader, array('debug' => true));

echo "\nWelcome to the Odin configuration script.\n";
echo "Please make sure you have write access to " . ROOT . " to create the required config.";

$template['APP_NAME'] = getInput("Please give your app a name");
$template['DB_REQUIRED'] = getInput("Does your app require a database?","boolean");
if($template['DB_REQUIRED']) {
    $template['DBTYPE'] = getInput("Which database would you like to use? ", "string", "", array("mysql","postgres","sqlite"));
    $template['DBHOST'] = getInput("DB host [locahost] ?", "string", "localhost");
    $template['DBPORT'] = getInput("DB port [3306] ?", "integer", "3306");
    $template['DBUSER'] = getInput("DB username");
    $template['DBPASS'] = getInput("DB password");
    $template['DBNAME'] = getInput("DB name");
} else {
    $template['DBTYPE'] = "";
    $template['DBHOST'] = "";
    $template['DBPORT'] = "";
    $template['DBUSER'] = "";
    $template['DBPASS'] = "";
    $template['DBNAME'] = "";
}
$template['USE_MEMCACHED'] = getInput("Do you use a memcache server?","boolean");
if($template['USE_MEMCACHED']) {
    $template['MEMCACHED_HOST'] = getInput("Memcache host [locahost] ?", "string", "localhost");
    $template['MEMCACHED_PORT'] = getInput("Memcache port [11211] ?", "integer", "11211");
} else {
    $template['MEMCACHED_HOST'] = "";
    $template['MEMCACHED_PORT'] = "";
}
$template['USE_REDIS'] = getInput("Do you use a redis server?","boolean");
if($template['USE_REDIS']) {
    $template['REDIS_HOST'] = getInput("Redis host [locahost] ?", "string", "localhost");
    $template['REDIS_PORT'] = getInput("Redis port [6379] ?", "integer", "6379");
} else {
    $template['REDIS_HOST'] = "";
    $template['REDIS_PORT'] = "";
}
$template['MIN_PASSWORD_LENGTH'] = getInput("Please supply a minimum password length [12]", "integer", "12");
$template['SESSION_TIMEOUT'] = getInput("Session timeout in minutes [30]", "integer", "30");

$use_otp = getInput("Do you want to use 2 factor authentication?","boolean");
if($use_otp){
    $template['OTP_SECRET'] = getInput("Please supply your secret key, required for generating OTP [random]","string","random");
    if($template['OTP_SECRET'] == "random") {
        $template['OTP_SECRET']  = \Odin\Util::getRandomString(64,false);
    }
    $template['OTP_BASE32_SECRET']  = \Base32\Base32::encode($template['OTP_SECRET']);
} else {
    $template['OTP_SECRET']  = "";
    $template['OTP_BASE32_SECRET']  = "";
}

$generate_jwt = getInput("Do you want to generate a JWT?","boolean");
if($generate_jwt){
    $template['JWT_SECRET'] = getInput("Please supply your JWT secret key [random]","string","random");
    if($template['JWT_SECRET'] == "random") {
        $template['JWT_SECRET'] = \Odin\Util::getRandomString(64,false);
    }
} else {
    $template['JWT_SECRET'] = "";
}

$new_config = fopen(CONF_FILE, "w") or die("Unable to open file: " . CONF_FILE);
fwrite($new_config, $twig->render('config.inc.php.example', $template));
fclose($new_config);
echo "\ndone\n";

