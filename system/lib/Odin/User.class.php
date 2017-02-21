<?php
/**
 * OdinUser class, basic user functionality
 * 
 * @package     Odin Framework
 * @author      Wayne Oliver <wayne@open-is.co.za>
 * @copyright   Wayne Oliver <wayne@open-is.co.za> 2011 - 2017
 * @license     http://www.opensource.org/licenses/BSD-2-Clause
 *
 **/
 
namespace Odin;
 
class User 
{
	/**
	 * A database connection tha is responsible for all data 
	 * persistence and retrieval.
	 * @var PDO
	 */  
	protected $db = null;
	
	/**
	 * The logger that handles writing logs to file.
	 * @var Log
	 */  
	protected $log = null;
	
	/**
	 * The user's id 		if (!$uid = User::verify_user($email))
			$uid = User::create(1,$email,3,$first_name,$last_name);
	 * @var int
	 */  
	protected $id = null;
 
	/**
	 * A boolean flag on whether or not the user is deleted
	 * @var bool
	 */  
	protected $deleted = false;
	
	/**
	 * The date the user was added to the system
	 * @var string
	 */  
	protected $created_date = null;

	/**
	 * The id of the user that added this user to the system. 
	 * @var int
	 */  
	protected $created_by_id = null;
	
	/**
	 * The id of the autentication provider 
	 * @var int
	 */  
	protected $auth_provider_id = null;	

	/**
	 * The name of the autentication provider 
	 * @var string
	 */  
	protected $auth_provider = null;		
	
	/**
	 * The user's email address and username
	 * @var string
	 */  
	protected $email = null;
	
	/**
	 * The user's unique identifier
	 * @var string
	 */  
	protected $slug = null;
	
	/**
	 * The user's first name
	 * @var string
	 */  
	protected $first_name = null;	
	
	/**
	 * The user's last name
	 * @var string
	 */  
	protected $last_name = null;
	
	/**
	 * The user's name
	 * @var string
	 */  
	protected $name = null;	
	
	/**
	 * The date the user last logged in
	 * @var string
	 */  
	protected $last_login = null;
	
	/**
	 * Temp storage for setting a password at user creation
	 * @var string
	 */  
	protected $password = null;
	
	/**
	 * The user's photo URL
	 * @var string
	 */  
	protected $photo_url = null;
  
	/**
	 * A boolean flag on whether or not the user needs a pass change
	 * @var bool
	 */  
	protected $change_password  = false;

	/**
	 * Class constructor
	 * 
	 * @param array	$params Config options, db and log objects
	 * @param int $id User's database id
	 * @param string $slug	User's unique identifier
	 * @param string $email User's email address
	 * 
	 * @return void
	 **/
	public function __construct($params=array(),$id=null,$slug= '',$email='')
	{
		// Populate default params like db/log
		foreach($params as $key => $value) {
			$this->$key = $value;
		}

		// Load user details via id, slug or email
		if (is_numeric($id)) {
			$this->id = $id;
			$this->getDetailsById();			
		} else if ($id == null && $slug != ''){
			$this->slug = $slug;
			$this->getDetailsBySlug();
		} else if ($id == null && $slug == '' && $email != ''){
			$this->email = $email;
			$this->getDetailsByEmail();
		}
	}	

	/*
	 * Get user details from data base using the database id
	 *
	 * @return bool
	 */
	private function getDetailsById()
	{
  
		try {
			$stmt = $GLOBALS['db']->prepare("SELECT u.*, ap.provider,
											ap.name as ap_name, uc.photo_url
											FROM user u 
											LEFT JOIN user_custom uc
												ON u.id = uc.user_id 
											LEFT JOIN authentication_provider ap
												ON u.authentication_provider_id = ap.id 
											WHERE u.id=:id");
			$stmt->bindValue(':id', $this->id);
			$stmt->execute();
			$result = $stmt->fetch();
		} 
		catch (Exception $e) {
			$this->log->logError($e);
			return false;
		}
		// Populate the object
		$this->populateVariables($result);
		return true;
	}
	
	/*
	 * Get user details from data base using the user's unique identifier
	 *
	 * @return bool
	 */
	private function getDetailsBySlug()
	{
  
		try {
			$stmt = $GLOBALS['db']->prepare("SELECT u.*, ap.provider,
											ap.name as ap_name, uc.photo_url
											FROM user u 
											LEFT JOIN user_custom uc
												ON u.id = uc.user_id 
											LEFT JOIN authentication_provider ap
												ON u.authentication_provider_id = ap.id 
											WHERE u.slug=:slug");
			$stmt->bindValue(':slug', $this->slug);
			$stmt->execute();
			$result = $stmt->fetch();
		} 
		catch (Exception $e) {
			$this->log->logError($e);
			return false;
		}
		// Populate the object
		$this->populateVariables($result);
		return true;
	}
  
	/*
	 * Get user details from data base using the user's email
	 *
	 * @return bool
	 */
	private function getDetailsByEmail()
	{
  
		try {
			$stmt = $GLOBALS['db']->prepare("SELECT u.*, ap.provider,
											ap.name as ap_name, uc.photo_url
											FROM user u 
											LEFT JOIN user_custom uc
												ON u.id = uc.user_id 
											LEFT JOIN authentication_provider ap
												ON u.authentication_provider_id = ap.id 
											WHERE u.email=:email");
			$stmt->bindValue(':email', $this->email);
			$stmt->execute();
			$result = $stmt->fetch();
		} 
		catch (Exception $e) {
			$this->log->logError($e);
			return false;
		}
		// Populate the object
		$this->populateVariables($result);
		return true;
	}

	/*
	* Popuplate the user object from the database results
	*
	* @return void
	*/
	private function populateVariables($result) {

		if (!empty($result)) {

			$this->id               = $result['id'];
			$this->deleted          = $result['deleted'];
			$this->created_date     = $result['created_date'];
			$this->created_by_id    = $result['created_by_id'];
			$this->auth_provider_id = $result['authentication_provider_id'];		
			$this->auth_provider    = $result['ap_name'];		
			$this->email            = $result['email'];
			$this->first_name       = $result['first_name'];
			$this->last_name        = $result['last_name'];
			$this->name             = $this->first_name . ' ' . $this->last_name;
			$this->slug             = $result['slug'];
			$this->last_login       = $result['last_login'];
			$this->photo_url        = $result['photo_url'];
			$this->change_password  = $result['change_password'];  
		}
	}
	
	/*
	 * Get an array of *all* users on the system
	 *
	 * @return array
	 */
	private function getAll()
	{
		try {
			$stmt = $this->db->prepare("SELECT u.id,u.email,ap.name,
											u.first_name,u.last_name,u.slug,
											u.last_login
										FROM user u 
										LEFT JOIN autentication_provider ap
											ON u.autentication_provider_id = ap.id");
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $rows;			
		} catch (Exception $e) {
			$this->log->logError($e);
			return array();
		}
	}

	/*
	 * Get an array of all active users on the system
	 *
	 * @return array
	 */
	private function getActive()
	{
		try {
			$stmt = $this->db->prepare("SELECT u.id,u.email,ap.name,
											u.first_name,u.last_name,u.slug,
											u.last_login
										FROM user u 
										LEFT JOIN autentication_provider ap
											ON u.autentication_provider_id = ap.id
										WHERE u.deleted = 0");
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $rows;			
		} catch (Exception $e) {
			$this->log->logError($e);
			return array();
		}
	}
	
	/*
	 * Get an array of all deleted users in the system
	 *
	 * @return array
	 */
	private function getDeleted()
	{
		try {
			$stmt = $this->db->prepare("SELECT u.id,u.email,ap.name,
											u.first_name,u.last_name,u.slug,
											u.last_login
										FROM user u 
										LEFT JOIN autentication_provider ap
											ON u.autentication_provider_id = ap.id
										WHERE u.deleted = 1");
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $rows;			
		} catch (Exception $e) {
			$this->log->logError($e);
			return array();
		}
	}	

	/*
	 * Get an array of authentication providers
	 *
	 * @params array $params containing paramns['db'] and params['log']
	 *
	 * @return array
	 */
	public static function getAuthenticationProviders($params)
	{
		try {
			$db  = $params['db'];
			$log = $params['log'];
		} catch (Exception $e) {
			return array();
		}
		
		try {
			$stmt = $db->prepare("SELECT id,name FROM authentication_provider WHERE deleted=0");
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $rows;			
		} catch (Exception $e) {
			$log->logError($e);
			return array();
		}
	}		

	/*
	 * Check if a slug is already in use
	 *
	 * @params array $params containing paramns['db'] and params['log']
	 * @params string $slug Slug you wish to check
	 *
	 * @return bool
	 */	
	public static function verifySlug($params,$slug)
	{
		
		try {
			$db  = $params['db'];
			$log = $params['log'];
		} catch (Exception $e) {
			return false;
		}		

		try {

			$stmt = $db->prepare("SELECT id FROM user WHERE slug=:slug"); 
										   
			$stmt->bindValue(':slug', $slug);  
			$stmt->execute();
			
			if($result = $stmt->fetch()){
				return true;
			} else {
				return false;
			}
		
		} catch (Exception $e) {
			$log->logError($e);
			return false;
		}
	} 

	/*
	 * Check if an email address is already registered
	 *
	 * @params array $params containing paramns['db'] and params['log']
	 * @params string $email Email address you wish to check
	 *
	 * @return bool
	 */	
	public static function verifyEmail($params,$email)
	{
		
		try {
			$db  = $params['db'];
			$log = $params['log'];
		} catch (Exception $e) {
			return false;
		}		

		try {

			$stmt = $db->prepare("SELECT id FROM user WHERE email=:email"); 
										   
			$stmt->bindValue(':email', $email);  
			$stmt->execute();
			
			if($result = $stmt->fetch()){
				return true;
			} else {
				return false;
			}
		
		} catch (Exception $e) {
			$log->logError($e);
			return false;
		}
	} 
	


	/**
	 * Verify email/username and password combination
	 * 
	 * @param string $email User's email address
	 * @param string $password User's password
	 * 
	 * @return bool Do the username and password combination match?
	 * 
	 **/
	public function verifyPassword($email, $password)
	{
		try {
			$stmt = $this->db->prepare("SELECT u.hash,u.salt
										FROM user u 
										WHERE email=:email
										AND u.authentication_provider_id = 1 
										AND u.deleted=0");
										
			$stmt->bindValue(':email', $email);
			$stmt->execute();
			$result = $stmt->fetch();
			
			if ($result['hash'] == Util::getHash($password,$result['salt'])) {
				$this->setLastLogin();
				return true;
			}
		} catch (Exception $e) {
			$this->log->logError($e);
			return array();
		}
	}
	
	/**
	 * Reset the User's password via email
	 * 
	 * @param none
	 * 
	 * @return bool Succesfully reset or not
	 * 
	 **/
	public function resetPassword()
	{
		if (!is_numeric($this->id)) {
			return false;
		}
		
		try {
			
			if (defined(MIN_PASSWORD_LENGTH)) {
				$len = MIN_PASSWORD_LENGTH;
			} else {
				$len = 10;
			}
			
			$salt = Util::getSalt();
			$pass = Util::getRandomString($len);
			$hash = Util::getHash($pass,$salt);
			
			$stmt = $this->db->prepare("UPDATE user 
										SET salt=:salt, 
											hash=:hash,change_password=1 
										WHERE email=:email");

			$stmt->bindValue(':salt',  $salt);
			$stmt->bindValue(':hash',  $hash);
			$stmt->bindValue(':email', $this->email);
										
			if ($stmt->execute()) {			
				
				$variables['username']   = $this->email; 
				$variables['first_name'] = $this->first_name; 
				$variables['password']   = $pass; 
				$variables['base_url']   = BASE_URL;
				$subject                 = APP_NAME . " password reset";
				
				$template = "email" . DIRECTORY_SEPARATOR . "password-reset.twig";
				
				
				$text_version = "
Hello " . $this->first_name . ",

Your password for " . BASE_URL . " has been reset.
Please logon and change your password as soon asp possible.

Username: " . $this->email . "
Password: $pass

Thank you";
				
				Util::sendTemplateMail($template,$subject,$this->email,$variables,$text_version='');

			} else {
				$this->log->logError("PASSWORD: Reset failed for user " . $this->email);
				return false;
			}
		} catch (Exception $e) {
			$this->log->logError($e);
			return array();
		}
	}	

	/**
	 * Set the User's password 
	 * 
	 * @param string $password
	 * 
	 * @return bool Succesfully set or not
	 * 
	 **/
	public function setPassword($password)
	{
		// Not a valid user
		if ($this->id == null)
			return false;				

		$salt = Util::getSalt();
		$hash = Util::getHash($password, $salt);

		try {
			$stmt = $this->db->prepare("UPDATE user 
										SET salt=:salt,hash=:hash,change_password=0 
										WHERE id=:id");
		
			$stmt->bindValue(':salt', $salt);
			$stmt->bindValue(':hash', $hash);
			$stmt->bindValue(':id',   $this->id);
			
			if ($stmt->execute()) {
				$this->log->logDebug("PASSWORD: User:" . $this->email . " changed his/her password");
				return true;
			} else {
				return false;
			}
			
		} catch (Exception $e) {
			$this->log->logError($e);
			return false;
		}
	}

	/**
	 * Set the User's last_login timestamp
	 * 
	 * @param none
	 * 
	 * @return bool Succesfully set or not
	 * 
	 **/
	function setLastLogin()
	{
		// Not a valid user
		if ($this->id == null)
			return false;
			
		$this->last_login = date('Y-m-d H:i:s');

		try {
			$stmt = $GLOBALS['db']->prepare("UPDATE user SET last_login=now() WHERE id=:id");
			$stmt->bindValue(':id', $this->id);
				
			if ($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		} catch (Exception $e) {
			$this->log->logError($e);
			return false;
		}
	}

	/**
	 * Set user as logged in 
	 * 
	 * @param none
	 * 
	 * @return bool Succesfully set or not
	 * 
	 **/
	public function setLoggedIn()
	{
		$_SESSION[APP_NAME]['AUTHENTICATED'] = TRUE;
		$_SESSION[APP_NAME]['EMAIL'] = $this->email;

		try {
			$this->getDetailsByEmail();
			return true;
		} catch (Exception $e) {
			$_SESSION[APP_NAME]['AUTHENTICATED'] = FALSE;
			$this->log->logError($e);;
			return false;
		}
	}

	/**
	 * Set user as logged out 
	 * 
	 * @param none
	 * 
	 * @return bool Succesfully set or not
	 * 
	 **/
	function logout()
	{
		if(isset($_SESSION[APP_NAME]['AUTHENTICATED'])) {
			$_SESSION[APP_NAME]['AUTHENTICATED'] = false;
			session_destroy();
			header('Location: ' . BASE_URL );
		}	
		session_destroy();
		header('Location: ' . BASE_URL );
	}

	/**
	 * Check if the user is logged in
	 * 
	 * @param none
	 * 
	 * @return bool
	 * 
	 **/  
	function isLoggedIn()
	{
		if(isset($_SESSION[APP_NAME]['AUTHENTICATED']) 
			&& $_SESSION[APP_NAME]['AUTHENTICATED'] == TRUE 
			&& $_SESSION[APP_NAME]['EMAIL'] == $this->email) {

			$this->setLoggedIn();
			return true;

		} else {
			return false;
		}
	}


	/**
	 * Quick redirect user
	 * 
	 * @param none
	 * 
	 * @return void
	 * 
	 **/  
	function redirect($path)
	{
		header( 'Location: ' . BASE_URL . $path );
	}

	/**
	 * Check if the user is logged in
	 * 
	 * @param none
	 * 
	 * @return bool
	 * 
	 **/ 
	public function create()
	{

		$slug   = Util::slugify($this->first_name . " " . $this->last_name);
		$params = array('db' => $this->db, 'log' => $this->log);

		if(User::verifySlug($params, $slug)) {
			$counter = 1;
			$original_slug = $slug;
			$slug = $original_slug . '-' . $counter;
			while(User::verifySlug($params, $slug)) {
				$counter++;
				$slug = $original_slug . '-' . $counter;
			}
		}   

		try { 
			$stmt = $this->db->prepare("INSERT INTO user 
											(created_by_id,email,
											authentication_provider_id,
											first_name,last_name,slug)
										VALUES(:created_by_id,:email,
											:auth_prov_id,
											:first_name,:last_name,:slug)");

			$stmt->bindValue(':created_by_id', $this->created_by_id);
			$stmt->bindValue(':auth_prov_id',  $this->auth_provider_id);        
			$stmt->bindValue(':email',         $this->email);  
			$stmt->bindValue(':first_name',    $this->first_name);							
			$stmt->bindValue(':last_name',     $this->last_name);
			$stmt->bindValue(':slug',          $slug);


			if($stmt->execute()) { 

				// create user_custom entry
				$stmt2 = $this->db->prepare("INSERT INTO user_custom (user_id) VALUES (:new)");
				$stmt2->bindValue(':new', $this->db->lastInsertId());
				$stmt2->execute();

				if($this->auth_provider_id == 1) {

					// Only bother with the password if the user
					if ($this->password == null) {
						$this->password = Util::getRandomString(MIN_PASSWORD_LENGTH);
						$this->setPassword($this->password);
					} else {
						$this->setPassword($this->password);
					}
					
				} else if ($auth_provider_id == 2) {
					$this->password = "Please use the Google login";
				} else if ($auth_provider_id == 3) {
					$this->password = "Please use the Facebook login";
				} else if ($auth_provider_id == 4) {
					$this->password = "Please use the Twitter login";
				}

				
				$variables['username']   = $this->email; 
				$variables['first_name'] = $this->first_name; 
				$variables['password']   = $this->password; 
				$variables['base_url']   = BASE_URL;
				$subject                 = APP_NAME . " Account created";
				
				$template = "email" . DIRECTORY_SEPARATOR . "password-reset.twig";
				
				$text_version = "
Hello " . $this->first_name . ",

Your accout for " . BASE_URL . " has been activated.
Please logon and change your password as soon asp possible.

Username: " . $this->email . "
Password: " . $this->password . "

Thank you";
				
				Util::sendTemplateMail($template,$subject,$this->email,$variables,$text_version='');
				
				return true;
			} else {
				return false;
			}
		} catch (Exception $e)  {
			$this->log->logError($e);
			return false;
		}
	}
  

	/**
	 * Update just the user's core attributes first_name, last_name, email
	 * 
	 * @param none
	 * 
	 * @return bool Update success
	 * 
	 **/
	public function save() 
	{
		// Not a valid user
		if ($this->id == null)
			return false;		

		$slug = User::slugify($this->first_name . " " . $this->last_name);

		if ($this->slug != $slug) {

			if(User::verify_slug($slug)) {
				$counter = 1;
				$original_slug = $slug;
				$slug = $original_slug . '-' . $counter;
				while(User::verify_slug($slug)) {
					$counter++;
					$slug = $original_slug . '-' . $counter;
				}
			}
		}

		try {
			$stmt = $GLOBALS['db']->prepare("UPDATE user 
											SET first_name=:first_name,
												last_name=:last_name,
												slug=:slug,
												email=:email 
											WHERE id=:id");
		   
			$stmt->bindValue(':first_name',  $this->first_name);
			$stmt->bindValue(':last_name',   $this->last_name);      
			$stmt->bindValue(':slug',        $slug);
			$stmt->bindValue(':email',       $this->email);  
			$stmt->bindValue(':id',          $this->id);

			if ($stmt->execute()) {
				return true;
			} else {
				return false;
			}	
		} catch (Exception $e) {
			$this->log->logError($e);
			return false;
		}			
	}

	/**
	 * Update just the user's photo url
	 * 
	 * @param none
	 * 
	 * @return bool Update success
	 * 
	 **/
	public function savePhotoURL() {

		try {
			$stmt = $this->db->prepare("UPDATE user_custom 
										SET photo_url=:url 
										WHERE user_id=:id");

			$stmt->bindValue(':id',    $this->id);
			$stmt->bindValue(':fname', $this->photo_url);
			
			if ($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		} 
		catch (Exception $e) {
			$this->log->logError($e);
			return false;
	    }
	}

	/**
	 * Update just the user's first name
	 * 
	 * @param none
	 * 
	 * @return bool Update success
	 * 
	 **/
	public function saveFirstName() {

		try {
			$stmt = $this->db->prepare("UPDATE user 
										SET first_name=:fname 
										WHERE id=:id");

			$stmt->bindValue(':id',    $this->id);
			$stmt->bindValue(':fname', $this->first_name);
			
			if ($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		} 
		catch (Exception $e) {
			$this->log->logError($e);
			return false;
	    }
	}

	/**
	 * Update just the user's last name
	 * 
	 * @param none
	 * 
	 * @return bool Update success
	 * 
	 **/
	public function saveLastName() {

		try {
			$stmt = $this->db->prepare("UPDATE user 
										SET last_name=:lname 
										WHERE id=:id");

			$stmt->bindValue(':id',    $this->id);
			$stmt->bindValue(':lname', $this->last_name);
			
			if ($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		} 
		catch (Exception $e) {
			$this->log->logError($e);
			return false;
		}
	}     

	/**
	 * Mark the user account as deleted
	 * 
	 * @param none
	 * 
	 * @return bool Mark deleted successful
	 * 
	 **/
	public function delete () {

		if ($this->id == 0)
			return false;

		try {
			$stmt = $this->db->prepare("UPDATE user SET deleted=1 WHERE id=:id");

			$stmt->bindValue(':id', $this->id);
			
			if ($stmt->execute()) {
				return true;
			}
			else {
				return false;
			}
		} 
		catch (Exception $e) {
			$this->log->logError($e);
			return false;
		}
	}      

	/*
	 * Get and Set magic methods!
	 */
	public function __get($property) 
	{
		if (property_exists($this, $property)) { 
			return $this->$property;
		}
	}

	public function __set($property, $value) 
	{
		if (property_exists($this, $property)) { 
			$this->$property = $value;
		}
		return $this;
	}
}
