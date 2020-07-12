<?php
/**
 * Tenant class, basic tenancy for odin
 * 
 * @package     Odin Framework
 * @author      Wayne Oliver <wayne@open-is.co.za>
 * @copyright   Wayne Oliver <wayne@open-is.co.za> 2011 - 2020
 * @license     http://www.opensource.org/licenses/BSD-2-Clause
 *
 **/
 
namespace Odin;
 
class Tenant 
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
	 * Class constructor
	 * 
	 * @param array	$params Config options, db and log objects
	 * @param int $id User's database id
	 * @param string $slug	User's unique identifier
	 * @param string $email User's email address
	 * 
	 * @return void
	 **/
	public function __construct($params=array())
	{
		// Populate default params like db/log
		foreach($params as $key => $value) {
			$this->$key = $value;
        }
    }
}
