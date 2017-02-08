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
 
namespace Odin\Base;
 
class OdinUser 
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
	 * The user's id 
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
	 * @param array		Config options, db and log objects
	 * @param int		User's database id
	 * @param string	User's unique identifier
	 * @return void
	 **/
	public function __construct($params = array(), $id = null, $slug = '')
	{
		// Populate default params like db/log
		foreach($params as $key => $value) {
			$this->$key = $value;
		}

		// Load user details via id or slug
		if (is_numeric($id)) {
			$this->id = $id;
			$this->getDetailsById();			
		} else if ($id == null && $slug != ''){
			$this->slug = $slug;
			$this->getDetailsBySlug();
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
			$this->log->log_error($e);
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
			$this->log->log_error($e);
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
			$this->log->log_error($e);
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
			$this->enabled          = $result['enabled'];
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
	private function get_all()
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
		catch (Exception $e) {
			$this->log->log_error($e);
			return array();
		}
	}

	/*
	 * Get an array of all active users on the system
	 *
	 * @return array
	 */
	private function get_active()
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
		catch (Exception $e) {
			$this->log->log_error($e);
			return array();
		}
	}
	
	/*
	 * Get an array of all deleted users in the system
	 *
	 * @return array
	 */
	private function get_deleted()
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
		catch (Exception $e) {
			$this->log->log_error($e);
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
	public static function get_authentication_providers($params)
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
		catch (Exception $e) {
			$log->log_error($e);
			return array();
		}
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
      $stmt = $GLOBALS['db']->prepare("SELECT u.hash,u.salt 
                                       FROM user u 
                                       WHERE email=:email
                                       AND u.authentication_provider_id = 1 
                                       AND u.deleted=0 
                                       AND u.enabled=1");
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
                                       AND authentication_provider_id = 1                                       
                                       AND enabled=1 
                                       AND deleted=0");
      $stmt->bindValue(':email', $email);
      $stmt->execute();
      $result = $stmt->fetch();
      if ($result) {
        $salt     = User::get_salt();
        $password = User::random_string(MIN_PASSWORD_LENGTH);
        $hash     = User::get_hash($password, $salt);

        $stmt = $GLOBALS['db']->prepare("UPDATE user 
                                        SET salt=:salt, hash=:hash, 
                                          change_password=1
                                        WHERE email=:email");

        $stmt->bindValue(':salt',  $salt);
        $stmt->bindValue(':hash',  $hash);
        $stmt->bindValue(':email', $email);

        if ($stmt->execute()) {
          // TODO: Update audit log, send email
          
          $template = TEMPLATE_DIR . DS . 'email' . DS . 'password-reset.html';

          if(defined('EMAIL_FROM'))
            $from = EMAIL_FROM;
          else
            $from = 'no-reply@noreply.com';   
            
          if(file_exists($template)) {
            $GLOBALS['log']->log_debug("USER: Sending html notification to $email, reading template: $template "); 

            $fh = fopen($template, 'r');
            $html_message = fread($fh, filesize($template));
            fclose($fh);	
            $html_message = str_replace('{{BASE_URL}}', BASE_URL, $html_message);
            $html_message = str_replace('{{USERNAME}}', $email, $html_message);
            $html_message = str_replace('{{PASSWORD}}', $password, $html_message);	          
          }
          
          $text_message = "
Hello {{USERNAME}}

Your password has been reset.

Please use the details provided to log in and change your password as soon as possible.

URL: {{BASE_URL}}
Password: {{PASSWORD}}

For any queries or bug reporting please contact support          
          ";   
          
          $text_message = str_replace('{{BASE_URL}}', BASE_URL, $text_message);
          $text_message = str_replace('{{USERNAME}}', $email, $text_message);
          $text_message = str_replace('{{PASSWORD}}', $password, $text_message);	 
          
          $mail = new PHPMailer;
          $mail->isSendmail();
          $mail->setFrom($from, APP_NAME);
          $mail->addAddress($email); 
          $mail->isHTML(true); 
          $mail->Subject = APP_NAME . ' password reset';
          $mail->Body    = $html_message;
          $mail->AltBody = $text_message;

          $send = $mail->send();
          if ($send) {
            return TRUE;
          }
          else {
            $GLOBALS['log']->log_error("EMAIL: Could not send password reset email: $email \n". $mail->ErrorInfo);
            return FALSE;
          }
        }
        else {
          $GLOBALS['log']->log_error("EMAIL: Could not reset password: $email ");
          return FALSE;
        }
      }
      else {
        $GLOBALS['log']->log_error("EMAIL: Could not find user email: $email");
        return FALSE;
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
                                             change_password=0
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
		$_SESSION[APP_NAME]['AUTHENTICATED'] = TRUE;
		$_SESSION[APP_NAME]['EMAIL'] = $email;

    try {
      $this->email = $email;
      $this->get_details_by_email();
      return TRUE;
    } 
    catch (Exception $e) {
      $_SESSION[APP_NAME]['AUTHENTICATED'] = FALSE;
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
	}

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

  function is_logged_in($email = '') {
    if(isset($_SESSION[APP_NAME]['AUTHENTICATED']) 
          && $_SESSION[APP_NAME]['AUTHENTICATED'] == TRUE 
          && $_SESSION[APP_NAME]['EMAIL'] == $email) {

      $this->set_logged_in($email);
      return TRUE;

    } else {
      return FALSE;
    }
  }

  function get_groups() {
  
      try {
        $stmt = $GLOBALS['db']->prepare("SELECT g.id, g.user_group 
                                         FROM user_group_membership ugm 
                                           LEFT JOIN user_group g ON ugm.group_id = g.id 
                                         WHERE ugm.user_id=:id");
                                         
	    $stmt->bindValue(':id', $this->id);
	    $stmt->execute();
	    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
		  foreach ($rows as $row) {
        $this->groups[] = $row['id'];
      }
	  } 
	  catch (Exception $e) {
	    $GLOBALS['log']->log_error($e);
	  }			
	}	
  
  function get_group_roles() {
  
      try {
        $stmt = $GLOBALS['db']->prepare("SELECT r.role, 
                                                et.entity, 
                                                r.entity_id 
                                       FROM user_group_role_membership ugrm 
                                       LEFT JOIN user_role r 
                                         ON ugrm.role_id = r.id 
                                       LEFT JOIN entity_type et 
                                         ON r.entity_type_id = et.id 
                                       WHERE ugrm.deleted=0
                                       AND ugrm.group_id IN (:group_ids)");

	    $stmt->bindValue(':group_ids', implode(',',$this->groups));
	    $stmt->execute();
	    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
		foreach ($rows as $row) {
          $this->group_roles[] = array('role' => $row['role'], 
                                       'entity' => $row['entity'], 
                                       'entity_id' => $row['entity_id']);
        }
	  } 
	  catch (Exception $e) {
	    $GLOBALS['log']->log_error($e);
	  }			
	}	 
  
  function get_roles() {
  
      try {
        $stmt = $GLOBALS['db']->prepare("SELECT r.role, 
                                                et.entity, 
                                                r.entity_id 
                                       FROM user_role_membership urm 
                                       LEFT JOIN user_role r 
                                         ON urm.role_id = r.id 
                                       LEFT JOIN entity_type et 
                                         ON r.entity_type_id = et.id 
                                       WHERE urm.user_id=:id");
	    $stmt->bindValue(':id', $this->id);
	    $stmt->execute();
	    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
		foreach ($rows as $row) {
          $this->roles[] = array('role' => $row['role'], 
                                 'entity' => $row['entity'], 
                                 'entity_id' => $row['entity_id']);
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
   * Check the user exists
   */		
  public static function verify_user($email) {
    
    try {
      
      $stmt = $GLOBALS['db']->prepare("SELECT id 
                                       FROM user 
                                       WHERE email=:email"); 
                                       
      $stmt->bindValue(':email', $email);  
      $stmt->execute();
      if($result = $stmt->fetch()){
        return $result['id'];
      } else {
        return FALSE;
      }    
      
    } catch (Exception $e) {
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
    
  } 
  
  /*
   * Check the user exists
   */		
  public static function verify_slug($slug) {
    
    try {
      
      $stmt = $GLOBALS['db']->prepare("SELECT id 
                                       FROM user 
                                       WHERE slug=:slug"); 
                                       
      $stmt->bindValue(':slug', $slug);  
      $stmt->execute();
      if($result = $stmt->fetch()){
        return $result['id'];
      } else {
        return FALSE;
      }    
      
    } catch (Exception $e) {
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
    
  } 
  
  /*
   * Check the user has a role
   */		
  public static function verify_role($user_id,$role_id) {
    
    try {
      
      $stmt = $GLOBALS['db']->prepare("SELECT id 
                                       FROM user_role_membership 
                                       WHERE user_id=:user_id
                                       AND role_id=:role_id 
                                       AND deleted=0 "); 
                                       
      $stmt->bindValue(':user_id', $user_id);  
      $stmt->bindValue(':role_id', $role_id);  
      $stmt->execute();
      if($result = $stmt->fetch()){
        return $result['id'];
      } else {
        return FALSE;
      }    
      
    } catch (Exception $e) {
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
  }  
  
  /*
   * Check the user has a role
   */		
  public static function verify_role_deleted($user_id,$role_id) {
    
    try {
      
      $stmt = $GLOBALS['db']->prepare("SELECT id 
                                       FROM user_role_membership 
                                       WHERE user_id=:user_id
                                       AND role_id=:role_id 
                                       AND deleted=1"); 
                                       
      $stmt->bindValue(':user_id', $user_id);  
      $stmt->bindValue(':role_id', $role_id);  
      $stmt->execute();
      if($result = $stmt->fetch()){
        return $result['id'];
      } else {
        return FALSE;
      }    
      
    } catch (Exception $e) {
      $GLOBALS['log']->log_error($e);
      return FALSE;
    }
  }      

  /*
   * Create a new user
   */			
  public static function create($user_id,$email,
                                $auth_provider_id,$first_name,
                                $last_name,$password=''){
    
    $slug = User::slugify($first_name . " " . $last_name);
    
    if(User::verify_slug($slug)) {
      $counter = 1;
      $original_slug = $slug;
      $slug = $original_slug . '-' . $counter;
      while(User::verify_slug($slug)) {
        $counter++;
        $slug = $original_slug . '-' . $counter;
      }
    }   
  
    try { 
      $stmt = $GLOBALS['db']->prepare("INSERT INTO user 
                                       (created_by_id,email,  
                                        authentication_provider_id, 
                                        first_name,last_name,slug)
                                         
                                       VALUES(:user_id,:email,
                                              :authentication_provider_id, 
                                              :first_name,:last_name,:slug)");
                                              
      $stmt->bindValue(':user_id',    $user_id);
      $stmt->bindValue(':authentication_provider_id', $auth_provider_id);        
      $stmt->bindValue(':email',      $email);  
      $stmt->bindValue(':first_name', $first_name);							
      $stmt->bindValue(':last_name',  $last_name);
      $stmt->bindValue(':slug',       $slug);

      
      if($stmt->execute()) { 
        
        $new_user_id = $GLOBALS['db']->lastInsertId();
        // create user_custom entry
        $stmt2 = $GLOBALS['db']->prepare("INSERT INTO user_custom (user_id) VALUES (:new)");
        $stmt2->bindValue(':new', $new_user_id);
        $stmt2->execute();
        $new_user = new User($GLOBALS['params'],$new_user_id);
        
        if($auth_provider_id == 1) {
          
          // Only bother with the password if the user
          if ($password == '') {
            $password = User::random_string(MIN_PASSWORD_LENGTH);
            $new_user->set_password($password);
          }
          else {
            $new_user->set_password($password);
          }
        } else if ($auth_provider_id == 2) {
          $password = "Please use the Google login";
        }
        // Send a welcome mail
        $new_user->send_welcome_email($password);
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
  
  // Send welcome email
	public function send_welcome_email($password = "")
	{
	
    //Create the email to be sent with new password
    $to = $this->email;
    $subject = APP_NAME . ' Registration';	
    $template = TEMPLATE_DIR . DS . 'email' . DS . 'new-user.html';

    if(defined('EMAIL_FROM'))
       $from = EMAIL_FROM;
    else
      $from = 'no-reply@noreply.com';

    $headers = "From: $from";

    if(file_exists($template)) 
    {
      $this->log->log_debug("USER: Sending html notification to $to, reading template: $template "); 

      $fh = fopen($template, 'r');
      $html_message = fread($fh, filesize($template));
      fclose($fh);	
      $random_hash = md5(date('r', time())); 

      $headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\""; 
        
      $message = "
--PHP-alt-$random_hash; 
Content-Type: text/plain; charset=\"iso-8859-1\"
Content-Transfer-Encoding: 7bit

Welcome to " . APP_NAME . "

A new account has been created for you. Your account details and URL link are listed below (please save the link as a bookmark):

URL: " . BASE_URL . "
Username: $to
Password: $password

If your mail server is hosted by Google you can utilise the Google login feature for your convenience. 
Please note that by doing so your login password will be removed and only Google authentication will be able to be used.

Our portal is an ever evolving platform so any feedback is valuable to us.

For any queries or bug reporting please contact you administrator



--PHP-alt-$random_hash
Content-Type: text/html; charset=\"iso-8859-1\"
Content-Transfer-Encoding: 7bit

$html_message
      
      ";
        
      // replace the variables from the html
      $message = str_replace('{{BASE_URL}}', BASE_URL, $message);
      $message = str_replace('{{USERNAME}}', $to, $message);
      $message = str_replace('{{PASSWORD}}', $password, $message);				
      
      // Mail the new reset code
      if (@mail($to,$subject,$message,$headers))
        return true;

    } 
    else 
    {
      $GLOBALS['log']->log_debug("USER CREATED: Sending plain text notification to $to"); 
      $message = "

Welcome to " . APP_NAME . "

A new account has been created for you. Your account details and URL link are listed below (please save the link as a bookmark):

URL: " . BASE_URL . "
Username: $to
Password: $password

If your mail server is hosted by Google you can utilise the Google login feature for your convenience. 
Please note that by doing so your login password will be removed and only Google authentication will be able to be used.

Our portal is an ever evolving platform so any feedback is valuable to us.

For any queries or bug reporting please contact your administrator.
      ";

      // Mail the welcome message
      if (@mail($to,$subject,$message,$headers))
        return true;						

    }	
	}  
  


  /*
   * Update the user details
   */		
  function save() {
    
    $slug = User::slugify($this->first_name . " " . $this->last_name);
    
    if(User::verify_slug($slug)) {
      $counter = 1;
      $original_slug = $slug;
      $slug = $original_slug . '-' . $counter;
      while(User::verify_slug($slug)) {
        $counter++;
        $slug = $original_slug . '-' . $counter;
      }
    }        
    
    $this->last_error = '';
    // Not a valid user
    if (($this->id == 0)  || ($this->id == '') || ($this->id == null))
      return FALSE;

    try {
      $stmt = $GLOBALS['db']->prepare("UPDATE user 
                                       SET
                                         enabled=:enabled,
                                         first_name=:first_name,
                                         last_name=:last_name,                                       
                                         slug=:slug,
                                         email=:email
                                       WHERE id=:id");

      $stmt->bindValue(':enabled',     $this->enabled);        
      $stmt->bindValue(':first_name',  $this->first_name);
      $stmt->bindValue(':last_name',   $this->last_name);      
      $stmt->bindValue(':slug',        $slug);
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
   * Update users photo_url
   */  
  public function save_photo_url($url) {
    
    try {
      $stmt = $GLOBALS['db']->prepare("UPDATE user_custom 
                                       SET photo_url = :url 
                                       WHERE user_id=:id");
                                       
      $stmt->bindValue(':id',  $this->id);
      $stmt->bindValue(':url', $url);
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
	 * Update users first name
	 */  
	public function save_first_name() {

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
			$this->log->log_error($e);
			return false;
	    }
	}
  
	/*
	 * Update users first name
	 */  
	public function save_last_name() {

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
			$this->log->log_error($e);
			return false;
		}
	}     

	/*
	 * Mark user deleted
	 */  
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
			$this->log->log_error($e);
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