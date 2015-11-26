<?php
/**
 * User class, basic user functionality
 * 
 * @package     Odin Framework
 * @author      Wayne Oliver <wayne@open-is.co.za>
 * @copyright   Wayne Oliver <wayne@open-is.co.za> 2011 - 2014
 * @license     BSD
 ********************************** 80 Columns *********************************
 **/
 
class User {

  private $table_name      = 'user'; //strtolower(preg_replace('/\B([A-Z])/', '_$1', get_class($this)));
  private $id              = 0;
  private $deleted         = 0;
  private $enabled         = 0;
  private $created_date    = '';
  private $created_by_id   = '';
  private $modified_date   = '';
  private $modified_by_id  = '';	
  private $email           = '';
  private $slug            = '';
  private $first_name      = '';
  private $last_name       = '';
  private $name            = '';
  private $password        = '';
  private $last_login      = '';
  private $change_password = 0;
  private $roles           = array();
  private $last_error      = '';

  /**
   * Class constructor
   * @param int
   * @param 
   * @return void
   **/
  public function __construct($id = 0, $slug = '') {
    // Check if the table exists, if not create it
    try {
      $stmt = $GLOBALS['db']->prepare("SELECT 1 FROM user LIMIT 1");
      $stmt->execute();    
    } 
    catch (Exception $e) {
      // Table does not exist, needs to be created
      $GLOBALS['log']->log_debug("user does not exist, creating");
      $this->create_tables();   
    }		

    if (($id == 0) || ($id == NULL)){
      // If is set not set or set to zero, try use the slug to lookup the user
      if($slug != '') {
        $GLOBALS['log']->log_debug("ID: $id - Slug: $slug");
        $this->slug = $slug;
        $this->get_details_by_slug();
	  }
	  else {
	    $this->_id = 0;
      }
    }
    else {
      $this->id = $id;
      $this->get_details_by_id();
    }
  }	

  public function create_tables() {
    // Create the table in the database
    $query1_mysql = "CREATE TABLE IF NOT EXISTS user (
      `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
      `deleted` tinyint(1) NOT NULL DEFAULT '0',
      `enabled` tinyint(1) NOT NULL DEFAULT '1',      
      `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `created_by_id` int(11) unsigned NOT NULL DEFAULT '1',
      `modified_date` datetime DEFAULT NULL,
      `modified_by_id` int(11) unsigned DEFAULT NULL,
      `email` varchar(100) NOT NULL,
      `salt` varchar(150) NOT NULL,
      `hash` varchar(150) NOT NULL,
      `first_name` varchar(50) NOT NULL,
      `last_name` varchar(50) NOT NULL,
      `slug` varchar(100) NOT NULL,
      `last_login` datetime DEFAULT NULL,
      `change_password` tinyint(1) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_email` (`email`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

    $query2_mysql = "INSERT INTO `user` (`deleted`, `created_by_id`,  `first_name`, `last_name`,`slug`, `email`, `hash`, `salt`) VALUES 
                    (0, 1,'Wayne', 'Oliver','wayne-oliver', 'wayne@open-is.co.za', '', '');";  

    $query3_mysql = "CREATE TABLE IF NOT EXISTS role (
      `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
      `deleted` tinyint(1) NOT NULL DEFAULT '0',
      `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `created_by_id` int(11) unsigned NOT NULL DEFAULT '1',
      `module` varchar(25) NOT NULL,
      `role` varchar(30) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_role` (`role`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

    $query4_mysql = "INSERT INTO `role` (`module`, `role`) VALUES 
                    ('user','Administrator'),('user','Reports');";  

    $query5_mysql = "CREATE TABLE IF NOT EXISTS user_role (
      `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
      `deleted` tinyint(1) NOT NULL DEFAULT '0',
      `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `created_by_id` int(11) unsigned NOT NULL DEFAULT '1',
      `user_id` int(11) NOT NULL,
      `role_id` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

    $query6_mysql = "INSERT INTO `user_role` (`user_id`, `role_id`) VALUES 
                    (1,1);";  
   
    // SQLite
    $query1_sqlite = "CREATE TABLE IF NOT EXISTS user (
      id INTEGER PRIMARY KEY   AUTOINCREMENT,
      deleted INTEGER NOT NULL DEFAULT 0,
      enabled INTEGER NOT NULL DEFAULT 1,
      created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
      created_by_id INTEGER NOT NULL DEFAULT 1,
      modified_date DATETIME,
      modified_by_id INTEGER,
      email CHAR(100) NOT NULL,
      salt CHAR(150) NOT NULL,
      hash CHAR(150) NOT NULL,
      first_name CHAR(100) NOT NULL,
      last_name CHAR(100) NOT NULL,
      slug CHAR(100) NOT NULL,      
      last_login DATETIME,
      change_password INTEGER NOT NULL DEFAULT 0);";
    $query2_sqlite = "INSERT INTO user (deleted, created_by_id, first_name, last_name, email, hash, salt) VALUES 
                    (0, 1,'Wayne', 'Oliver', 'wayne@open-is.co.za', '', '');"; 


    $query3_sqlite = "CREATE TABLE IF NOT EXISTS role (
      id INTEGER PRIMARY KEY   AUTOINCREMENT,
      deleted INTEGER NOT NULL DEFAULT 0,
      created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
      created_by_id INTEGER NOT NULL DEFAULT 1,
      module CHAR(25) NOT NULL,
      role CHAR(30) NOT NUL);";

    $query4_sqlite = "INSERT INTO role (module, role) VALUES 
                    ('user','Administrator'),('user','Reports');"; 

    $query5_sqlite = "CREATE TABLE IF NOT EXISTS role (
      id INTEGER PRIMARY KEY   AUTOINCREMENT,
      deleted INTEGER NOT NULL DEFAULT 0,
      created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
      created_by_id INTEGER NOT NULL DEFAULT 1,
      user_id INTEGER NOT NULL,
      role_id INTEGER NOT NUL);";

    $query6_sqlite = "INSERT INTO user_role (user_id, role_id) VALUES 
                    (1,1);"; 

    // Postgresql
    $query1_pgsql = "CREATE TABLE IF NOT EXISTS \"user\" (
      id SERIAL PRIMARY KEY,
      deleted SMALLINT NOT NULL DEFAULT 0,
      enabled SMALLINT NOT NULL DEFAULT 1,      
      created_date TIMESTAMP DEFAULT now()::timestamp(0),
      created_by_id INTEGER NOT NULL DEFAULT 1,
      modified_date TIMESTAMP,
      modified_by_id INTEGER DEFAULT NULL,
      email VARCHAR(100) NOT NULL,
      salt VARCHAR(150) NOT NULL,
      hash VARCHAR(150) NOT NULL,
      first_name VARCHAR(50) NOT NULL,
      last_name VARCHAR(50) NOT NULL,
      slug VARCHAR(100) NOT NULL,      
      last_login TIMESTAMP,
      change_password SMALLINT NOT NULL DEFAULT 0);";
    $query2_pgsql = "INSERT INTO \"user\" (deleted, created_by_id, first_name, last_name, email, hash, salt) VALUES 
                    (0, 1,'Wayne', 'Oliver', 'wayne@open-is.co.za', '', '');"; 
 
    $query3_pgsql = "CREATE TABLE IF NOT EXISTS role (
      id SERIAL PRIMARY KEY,
      deleted SMALLINT NOT NULL DEFAULT 0,
      created_date TIMESTAMP DEFAULT now()::timestamp(0),
      created_by_id INTEGER NOT NULL DEFAULT 1,
      module VARCHAR(25) NOT NULL,
      role VARCHAR(30) NOT NULL);";

    $query4_pgsql = "INSERT INTO role (module, role) VALUES 
                    ('user','Administrator'),('user','Reports');"; 

    $query6_pgsql = "INSERT INTO user_role (user_id, role_id) VALUES 
                    (1,1);"; 
   
    $GLOBALS['log']->log_debug("Create Tables:");
    switch (DB_TYPE) {
      case 'mysql':
        $GLOBALS['log']->log_query("Query: $query1_mysql");
        $GLOBALS['db']->query($query1_mysql);
        $GLOBALS['log']->log_query("Query: $query2_mysql");
        $GLOBALS['db']->query($query2_mysql);
        $GLOBALS['log']->log_query("Query: $query3_mysql");
        $GLOBALS['db']->query($query3_mysql);
        $GLOBALS['log']->log_query("Query: $query4_mysql");
        $GLOBALS['db']->query($query4_mysql); 
        $GLOBALS['log']->log_query("Query: $query5_mysql");
        $GLOBALS['db']->query($query5_mysql);
        $GLOBALS['log']->log_query("Query: $query6_mysql");
        $GLOBALS['db']->query($query6_mysql); 
       break;
        
      case 'sqlite':
        $GLOBALS['log']->log_query("Query: $query1_sqlite");
        $GLOBALS['db']->query($query1_sqlite);
        $GLOBALS['log']->log_query("Query: $query2_sqlite");
        $GLOBALS['db']->query($query2_sqlite);
        $GLOBALS['log']->log_query("Query: $query3_sqlite");
        $GLOBALS['db']->query($query3_sqlite);
        $GLOBALS['log']->log_query("Query: $query4_sqlite");
        $GLOBALS['db']->query($query4_sqlite);
        $GLOBALS['log']->log_query("Query: $query5_sqlite");
        $GLOBALS['db']->query($query5_sqlite);
        $GLOBALS['log']->log_query("Query: $query6_sqlite");
        $GLOBALS['db']->query($query5_sqlite); 
       break;

      case 'pgsql':
        $GLOBALS['log']->log_query("Query: $query1_pgsql");
        $GLOBALS['db']->query($query1_pgsql);
        $GLOBALS['log']->log_query("Query: $query2_pgsql");
        $GLOBALS['db']->query($query2_pgsql);
        $GLOBALS['log']->log_query("Query: $query3_pgsql");
        $GLOBALS['db']->query($query3_pgsql);
        $GLOBALS['log']->log_query("Query: $query4_pgsql");
        $GLOBALS['db']->query($query4_pgsql);
        $GLOBALS['log']->log_query("Query: $query5_pgsql");
        $GLOBALS['db']->query($query5_pgsql);
        $GLOBALS['log']->log_query("Query: $query6_pgsql");
        $GLOBALS['db']->query($query6_pgsql);
        break;

      default:
        throw new Exception("Invalid database type: " . DB_TYPE);
    }
  }
  
  /*
   * Get user details
   */
  private function get_details_by_id() {
  
    try {
      $stmt = $GLOBALS['db']->prepare("SELECT * FROM user WHERE id=:id");
      $stmt->bindValue(':id', $this->id);
      $stmt->execute();
      $result = $stmt->fetch();
    } 
    catch (Exception $e) {
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
	// Populate the object
	$this->populate_variables($result);
  }
  
  /*
   * Get user details
   */
  private function get_details_by_slug() {
  
    try {
      $stmt = $GLOBALS['db']->prepare("SELECT * FROM user WHERE slug=:slug");
      $stmt->bindValue(':slug', $this->slug);
      $stmt->execute();
      $result = $stmt->fetch();
    }
    catch (Exception $e) {
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
    // Populate the object
    $this->populate_variables($result);
  }	

  /*
   * Popuplate the user object
   */
  private function populate_variables($result) {
    
    if (!empty($result)) {
    
      $this->id              = $result['id'];
      $this->deleted         = $result['deleted'];
      $this->enabled         = $result['enabled'];
      $this->created_date    = $result['created_date'];
      $this->created_by_id   = $result['created_by_id'];
      $this->modified_date   = $result['created_date'];
      $this->modified_by_id  = $result['created_by_id'];		
      $this->email           = $result['email'];
      $this->first_name      = $result['first_name'];
      $this->last_name       = $result['last_name'];
      $this->name            = $this->first_name . ' ' . $this->last_name;
      $this->slug            = $result['slug'];
      $this->last_login      = $result['last_login'];
      $this->change_password = $result['change_password'];
      $this->get_roles();
    }
    else {
      $this->id = 0;
    }
  }
	
  /*
   * Get a list of all the users
   */
  public static function get_all() {
    $stmt = $GLOBALS['db']->prepare("SELECT * FROM user");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }	

  public static function get_all_roles() {
    $stmt = $GLOBALS['db']->prepare("SELECT * FROM role");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }	

  /*
   * Get a list of all the users
   */
  public static function get_users() {
    $stmt = $GLOBALS['db']->prepare("SELECT * FROM user WHERE deleted=0");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }	

  /*
   * Get a list of all the active users
   */
  function get_active() {
    $stmt = $GLOBALS['db']->prepare("SELECT * FROM user WHERE enabled=1 AND deleted=0");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }	

  /*
   * Get a list of all the deleted users
   */	
  function get_deleted() {
    $stmt = $GLOBALS['db']->prepare("SELECT * FROM user WHERE deleted=1");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);		
    return $rows;
  }	

  /*
   * Random String Generator
   * @param integer length of string to generate
   * @return string A random string of characters
   */ 	
	public static function random_string($length=8,$special=TRUE) {
		// Characters to include in the returned string 
		$string = "abcdefghijklmnopqrstuvwxyz";
		$string = $string . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$string = $string . '1234567890';

    if ($special) {
      $string = $string . '!@#$%^-';
    }
		
		// Initialize an empty string
		$return_string = "";
		
		// Build the string
		for($i=0;$i < $length;$i++) {
			$return_string .= $string[mt_rand(0, strlen($string)-1)];
		}
		
		// Return the generated string
		return $return_string;		
	}

  /*
   * Salt Generator
   * @param none
   * @return string A password salt
   */ 	
	public static function get_salt() 
	{
		$salt = base64_encode(mcrypt_create_iv(24, MCRYPT_DEV_URANDOM));
		return $salt; //Return it
	}

  /**
   * Hash Generator 
   * @param password user's password
   * @param salt user's salt
   * @return string Salt & Password hash 
   **/ 	
	public static function get_hash($password,$salt) 
	{
		// USE SHA512
		return crypt($password, '$6$rounds=10000$' . $salt);
	}	

  /**
   * Verify Username and password
   * @param username user's email address
	 * @param string user's supplied password
   * @return bool 
   **/ 		
	function verify_password($email, $password)
	{
    try {
      $stmt = $GLOBALS['db']->prepare("SELECT hash,salt 
                                       FROM user 
                                       WHERE email=:email
                                       AND deleted=0 
                                       AND enabled=1");
      $stmt->bindValue(':email', $email);
      $stmt->execute();
      $result = $stmt->fetch();
      if ($result['hash'] == $this->get_hash($password,$result['salt'])) {
        $this->set_logged_in($email);
        return TRUE;
      }
    }
    catch (Exception $e) {
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
	}

	public static function reset_password($email) {
    try {
      $stmt = $GLOBALS['db']->prepare("SELECT id FROM user 
                                       WHERE email=:email 
                                       AND enabled=1 
                                       AND deleted=0");
      $stmt->bindValue(':email', $email);
      $stmt->execute();
      $result = $stmt->fetch();
      if ($result) {
        $salt = User::get_salt();
        $password = User::random_string(MIN_PASSWORD_LENGTH);
        $hash = User::get_hash($password, $salt);

        $stmt = $GLOBALS['db']->prepare("UPDATE user 
                                         SET salt=:salt, hash=:hash, 
                                             change_password=1, 
                                             modified_date=now() 
                                         WHERE email=:email");
        $stmt->bindValue(':salt', $salt);
        $stmt->bindValue(':hash', $hash);
        $stmt->bindValue(':email', $email);

        if ($stmt->execute()) {
          // TODO: Update audit log, send email
          $mail = new SimpleMail();
          $mail->setTo($email, 'Your Email')
               ->setSubject(APP_NAME . ' password reset')
               ->setFrom(EMAIL_FROM, APP_NAME)
               ->addGenericHeader('X-Mailer', 'PHP/' . phpversion())
               ->addGenericHeader('Content-Type', 'text/html; charset="utf-8"')
               ->setMessage('<strong>New Password: ' . $password .'</strong>')
               ->setWrap(100);
          $send = $mail->send();
          if ($send) {
            return TRUE;
          }
          else {
            $GLOBALS['log']->log_error("EMAIL: Could not send password reset email: $send");
            $this->last_error = "Password Reset, Email could not be sent";
            return FALSE;
          }
        }
        else {
          $GLOBALS['log']->log_error("EMAIL: Could not reset password: $send");
          $this->last_error = "Password could not be reset";
          return FALSE;
        }
      }
      else {
        $GLOBALS['log']->log_error("EMAIL: Could not send password reset email: $send");
        $this->last_error = "Password Reset, Email could not be sent";
      }
    }
    catch (Exception $e) {
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
  }

	public function set_password($password) {
    
		if ($this->id != 0) {
			$salt = $this->get_salt();
			$hash = $this->get_hash($password, $salt);
			
      try {
        $stmt = $GLOBALS['db']->prepare("UPDATE user 
                                         SET salt=:salt, hash=:hash, 
                                             change_password=0, 
                                             modified_date=now() 
                                         WHERE id=:id");
        $stmt->bindValue(':salt', $salt);
        $stmt->bindValue(':hash', $hash);
        $stmt->bindValue(':id',   $this->id);
        if ($stmt->execute()) {
          $GLOBALS['log']->log_debug("PASSWORD: User:" . $this->id . " changed his/her password");
          return TRUE;
        }
        else {
          return FALSE;
        }
      }
      catch (Exception $e) {
        $GLOBALS['log']->log_error($e);
        return FALSE;
      }
		}
		else 
		{
			$this->logout;
		}
	}

	function set_last_login()
	{
		if ($this->id != 0)
		{
			$this->last_login = date('Y-m-d H:i:s');
			
      try {
        $stmt = $GLOBALS['db']->prepare("UPDATE user SET last_login=now() WHERE id=:id");
        $stmt->bindValue(':id', $this->id);
        if ($stmt->execute()) {
          return TRUE;
        }
        else {
          return FALSE;
        }
      }
      catch (Exception $e) {
        $GLOBALS['log']->log_error($e);
        return FALSE;
      }
		}
		else 
		{
			$this->logout;
		}
	}

	public function set_logged_in($email)
	{
		$_SESSION[APP_NAME . "-" . CUSTOMER_NAME]['AUTHENTICATED'] = TRUE;
		$_SESSION[APP_NAME . "-" . CUSTOMER_NAME]['EMAIL'] = $email;

    try {
      $stmt = $GLOBALS['db']->prepare("SELECT * FROM user WHERE email=:email");
      $stmt->bindValue(':email', $email);
      $stmt->execute();
      $result = $stmt->fetch();
	    $this->populate_variables($result);
    } 
    catch (Exception $e) {
      $_SESSION[APP_NAME . "-" . CUSTOMER_NAME]['AUTHENTICATED'] = FALSE;
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
	}

	function logout()
	{
		if(isset($_SESSION[APP_NAME . "-" . CUSTOMER_NAME]['AUTHENTICATED']))
		{
			$_SESSION[APP_NAME . "-" . CUSTOMER_NAME]['AUTHENTICATED'] = false;
			session_destroy();
			header('Location: ' . BASE_URL );
		}	
		session_destroy();
		header('Location: ' . BASE_URL );
	}

  function is_logged_in($email = '') {
    if(isset($_SESSION[APP_NAME . "-" . CUSTOMER_NAME]['AUTHENTICATED']) 
          && $_SESSION[APP_NAME . "-" . CUSTOMER_NAME]['AUTHENTICATED'] == TRUE 
          && $_SESSION[APP_NAME . "-" . CUSTOMER_NAME]['EMAIL'] == $email) {

      $this->set_logged_in($email);
      return TRUE;

    } else {
      return FALSE;
    }
  }

  function get_roles() {
  
      try {
        $stmt = $GLOBALS['db']->prepare("SELECT r.role 
                                       FROM user_role ur 
                                       LEFT JOIN role r ON ur.role_id = r.id 
                                       WHERE ur.user_id=:id");
	    $stmt->bindValue(':id', $this->id);
	    $stmt->execute();
	    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
		foreach ($rows as $row) {
          $this->roles[] = $row['role'];
        }
	  } 
	  catch (Exception $e) {
	    $GLOBALS['log']->log_error($e);
	  }			
	}	
  
	function redirect($path)
	{
		header( 'Location: ' . BASE_URL . $path );
	}

  /*
   * Create a new user
   */			
  public static function create($email,$first_name,$last_name,$slug,$password='') {
  
    try { 
      $stmt = $GLOBALS['db']->prepare("INSERT INTO user 
                                       (created_by_id,email,
                                        first_name,last_name,slug)
                                         
                                       VALUES(:crt_id,:email,:first_name,
                                              :last_name,:slug)");
                                              
      $stmt->bindValue(':crt_id',     $GLOBALS['session_user_id']);
      $stmt->bindValue(':email',      $email);
      $stmt->bindValue(':first_name', $first_name);							
      $stmt->bindValue(':last_name',  $last_name);
      $stmt->bindValue(':slug',       $slug);

      
      if($stmt->execute()) { 
        
        $new_user_id = $GLOBALS['db']->lastInsertId();
        
        if ($password == '') {
          User::reset_password($email);
        }
        else {
          $new_user = new User($new_user_id);
          $new_user->set_password($password);
        }
        
        return $new_user_id;
      }
      else
        return FALSE;			
    }
    catch (Exception $e)  {
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
  }


  /*
   * Update the user details
   */		
  function save() {
    
    $this->last_error = '';
    // Not a valid user
    if (($this->id == 0)  || ($this->id == '') || ($this->id == NULL))
      return FALSE;

    try {
      $stmt = $GLOBALS['db']->prepare("UPDATE user 
                                       SET modified_by_id=:modifier_id,
                                       modified_date=now(),
                                       enabled=:enabled,
                                       first_name=:first_name,
                                       last_name=:last_name,                                       
                                       slug=:slug,
                                       email=:email
                                       WHERE id=:id");

      $stmt->bindValue(':modifier_id', $GLOBALS['session_user_id']);
      $stmt->bindValue(':enabled',     $this->enabled);        
      $stmt->bindValue(':first_name',  $this->first_name);
      $stmt->bindValue(':last_name',   $this->last_name);      
      $stmt->bindValue(':slug',        $this->slug);
      $stmt->bindValue(':email',       $this->email);  
      $stmt->bindValue(':id',          $this->id);
      
      if ($stmt->execute()) {
        if ($this->password != '')
          $this->set_password($this->password);
        
        return TRUE;
      }
      else {
        return FALSE;
      }	
    }
    catch (Exception $e) {
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }			
  }

  /*
   * Assign a role
   */			
  public static function assign_role($user_id,$role_id) {

    try { 
      $stmt = $GLOBALS['db']->prepare("INSERT INTO user_role 
                                       (user_id,role_id) 
                                       VALUES(:user_id,:role_id)");
                                       
      $stmt->bindValue(':user_id', $user_id);
      $stmt->bindValue(':user_id', $user_id);
      $stmt->bindValue(':role_id', $role_id);


      if($stmt->execute()) { 
        return $GLOBALS['db']->lastInsertId();
      }
      else
        return false;			
    }
    catch (Exception $e)  {
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
  }
  
  /*
   * enable user
   */  
  public function enable() {
    
    if ($this->id == 0)
      return FALSE;

    try {
      $stmt = $GLOBALS['db']->prepare("UPDATE user
                                       SET enabled = 1,
                                       modified_by_id=:modifier_id,
                                       modified_date=now()                                       
                                       WHERE id=:id");
                                       
      $stmt->bindValue(':modifier_id', $GLOBALS['session_user_id']);                                         
      $stmt->bindValue(':id', $this->id);
      if ($stmt->execute()) {
        return TRUE;
      }
      else {
        return FALSE;
      }
    } 
    catch (Exception $e) {
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
  }
  
  
  /*
   * Disable user
   */  
  public function disable () {
    
    if ($this->id == 0)
      return FALSE;

    try {
      $stmt = $GLOBALS['db']->prepare("UPDATE user
                                       SET enabled = 0,
                                       modified_by_id=:modifier_id,
                                       modified_date=now()                                       
                                       WHERE id=:id");
                                       
      $stmt->bindValue(':modifier_id', $GLOBALS['session_user_id']);                                       
      $stmt->bindValue(':id', $this->id);
      if ($stmt->execute()) {
        return TRUE;
      }
      else {
        return FALSE;
      }
    } 
    catch (Exception $e) {
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
  }
  
  /*
   * Mark user deleted
   */  
  public function delete () {
    
    if ($this->id == 0)
      return FALSE;

    try {
      $stmt = $GLOBALS['db']->prepare("UPDATE user
                                       SET deleted = 1,
                                       modified_by_id=:modifier_id,
                                       modified_date=now()
                                       WHERE id=:id");
                                       
      $stmt->bindValue(':modifier_id', $GLOBALS['session_user_id']);
      $stmt->bindValue(':id', $this->id);
      if ($stmt->execute()) {
        return TRUE;
      }
      else {
        return FALSE;
      }
    } 
    catch (Exception $e) {
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
  }    
      
  
  /*
   * Get and Set magic methods!
   */
  public function __get($property) {
    if (property_exists($this, $property)) { 
      return $this->$property;
    }
  }

  public function __set($property, $value) {
    if (property_exists($this, $property)) { 
      $this->$property = $value;
    }
    return $this;
  }	
}
