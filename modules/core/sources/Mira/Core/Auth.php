<?php

/**
 * Mira
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@gevega.com so we can send you a copy immediately.
 *
 * @access private
 * @category   Mira
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @see Zend_Auth
 */
require_once ('Zend/Auth.php');

/**
 * Utility class for a straight foward authentication using always the same adapter.
 * 
 * @access private
 * @category   Mira
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Auth extends Zend_Auth {
	
	protected $adapter; 
	protected $api; 
	
	/**
	 * @var Mira_Core_User
	 */
	public $user;
	
    public function __construct($api) 
    {
        // this is to find users
        $this->api = $api;
        // create the zend adapter
    	$dbAdapter = Zend_Registry::get(Mira_Core_Constants::REG_DBADAPTER);
    	$this->adapter = new Zend_Auth_Adapter_DbTable($dbAdapter, 'user_usr', 'email_usr', 'pass_usr');
    }
    
    public function isLoggued() 
    {
        return ($this->user !== null);
    }
    
    /**
     * @return boolean
     */
    public function login($email = null, $password = null) 
    {
        if ($email) {
            $user = $this->api->uemail($email);
            if ($user) {
                $salt_usr = $user->salt_usr;        
                $data = array ('pass_usr' => $password,
                               'salt_usr' => $salt_usr,
                               'email_usr' => $email);
                $data = Mira_Utils_PasswordEncryption::encryptPassword($data);
                $password = $data['pass_usr'];
            	$this->adapter->setIdentity($email);
            	$this->adapter->setCredential($password);
            	$result = parent::authenticate($this->adapter);
            	if ($result->isValid()) {
            	    $this->user = $user;
            	    return true;
            	} else {
            	    $this->user = null;
            	    return false;
            	}
            } else {
        	    $this->user = null;
        	    return false;
            }
        } else {
            if ($this->hasIdentity()) { 
                $email = $this->getIdentity();
                $this->user = $this->api->uemail($email);
                return true;
            } else {
                $this->user = null;
        	    return false;
            }
        }
    }
    
    public function logout()
    {
        $this->clearIdentity();
    }
}
