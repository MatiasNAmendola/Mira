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
 * @category   Mira
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * Defines a user of this Mira installation
 *
 * Users are used in Mira's security system (RBAC security, see {@link Mira_Core_Scope}
 *   - to define object owners:
 * <code>
 * $api = new Mira();
 * $vega = $api->vname("My Object");
 * $user = $api->uname("Paul");
 * $vega->owner = $user;
 * </code>
 *   - or to define roles on objects:
 * <code>
 * $vega->scope->addUserRole($user, "editor");
 * </code>
 * 
 * @category   Mira
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_User extends Mira_Utils_Pretty_Row
{
    /**
     * this is used by AMF to specify remote AS classname
     * @access private
     * @var string
     */
    public $_explicitType = "com.vega.core.api.vega.User";
    
    const CONTACT_VEGATYPE_ID = 7;

	// @var Mira
	private $api;
	// @var Mira_Utils_Event_CommandBus
	private $bus;
    
    // This is te internal vega used to save extensible properties such as user's phone, address
    // and so on. This is accessible by doing <code>$user->contact->phone</code> for instance.
    // @var Mira_Core_Vega
    private $_contact;
    
    // We cache the initial values to detect when the password has changed (and then encrypt it)
    // @var string
    private $_initalPassword;
    // @var string
    private $_initalAccount;
    
    /**
     * This should not be instantiated directly. Use {@link Mira::selectUsers()} or {@link Mira::createUser()}
     * A Mira_Core_User instance exposes those properties (you won't have them by autocomplete / intellisense
     * as they are accessed by magic php functions) :
     *   - id
     *   - email
     *   - account : for account status i.e. "validated" or "validating"
     *   - creationDate
     *   - contact : a Mira_Core_Contact exposing extensible details about this user (phone, address etc...)
     *   - password : encrypted password given at account creation
     *   - salt : salt used for password generation
     *   - token : token used for email confirmation / password recovery
     * 
     * @param $config
     */
    public function __construct($config)
 	{
 	    // this table exposed properties
 	    $properties = array();
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "id", false, true, "baseProperty", "id_usr");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "email", false, true, "baseProperty", "email_usr");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "account", false, true, "baseProperty", "account_status_usr");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "creationDate", false, true, "baseProperty", "date_created_usr");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "contact", false, true, "contact");
 	    
 	    // those are hidden
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "password", false, true, "baseProperty", "pass_usr", true);
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "salt", false, true, "baseProperty", "salt_usr", true);
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "token", false, true, "baseProperty", "token_usr", true);

		// API
		$this->api = Zend_Registry::get(Mira_Core_Constants::REG_API); 
		// BUS
		$this->bus = Zend_Registry::get(Mira_Core_Constants::REG_BUS); 
 	    
 	    parent::__construct($config, $properties);
 	}
 	
 	/**
 	 * @access private
 	 */
    public function init()
    {
        parent::init();
        
        if (!empty($this->id_usr)) {
            $this->_initalPassword = $this->pass_usr;
            $this->_initalAccount = $this->account_status_usr;
            if (!empty($this->id_vg_usr)) {
                $this->_contact = $this->api->vid($this->id_vg_usr);
            }
        }
    }

    /**
     * internal
     * 
     * This method should not be called directly. For instance, to change the email
     * do 
     * <code>
     * $user->email = 'new value';
     * $user->save();
     * </code>
     * 
     * @access private 
     * @param string $localName 
     * @param mixed $value 
     */
    public function setBaseProperty($localName, $value)
    {
        if ($localName == "id_usr" || $localName == "salt_usr" || $localName == "token_usr") {
            throw new Exception("can't change $localName");
            return;
        }
        $this->$localName = $value;
    }
    
    /**
     * @access private
     * @param string $localName 
     * @return mixed
     */
    public function getBaseProperty($localName)
    {
        return parent::__get($localName);
    }
    
    /**
     * @access private
     * @return Mira_Core_Vega
     */
    public function getContact()
    {
        if (!$this->_contact) {
            $this->_contact = $this->api->createVega(self::CONTACT_VEGATYPE_ID, $this->email_usr, $this);
        }
        return $this->_contact;
    }    
    
    
    /**
     * @return integer
     */
    public function save()
    {
        $isNew = empty($this->id_usr);
        $contact = $this->getContact();
        
        if ($isNew) {
            if (Mira_Utils_String::isEmpty($this->account_status_usr)) 
                $this->account_status_usr = 'validating';
            if (Mira_Utils_String::isEmpty($this->token_usr)) 
                $this->token_usr = Mira_Utils_String::randomString(20, "alphanumeric");
        }
        
        // if a new password has been set, then we need to encrypt it before
        // passing it to the database  
        if ($isNew || $this->pass_usr != $this->_initalPassword) {
            $salt = Mira_Utils_PasswordEncryption::generateSalt($this->email_usr);
            $encryptedPassword = Mira_Utils_PasswordEncryption::encrypt($this->pass_usr, $salt);

            $this->pass_usr = $encryptedPassword;
            $this->salt_usr = $salt;
        }
        
        parent::save();
        
        // create associated contact vega
        if ($isNew) {
            if ($contact->name === null) {
                $contact->name = $this->email;
            }
            $contact->setOwner($this->id_usr);
            $contact->scope->addUserRole($this->id_usr, "editor");
            $contact->email = $this->email_usr;
            $contact->save();
        }
        
        if ($contact->isDirty()) $contact->save();
        $this->id_vg_usr = $contact->id;
        parent::save();
        
        if ($isNew) {
            $this->bus->dispatchEvent(new Mira_Core_Event_UserEvent(Mira_Core_Event_UserEvent::CREATE, $this));
        } else {
            $this->bus->dispatchEvent(new Mira_Core_Event_UserEvent(Mira_Core_Event_UserEvent::EDIT, $this));
            if ($this->account_status_usr == "validated" && $this->account_status_usr != $this->_initalAccount) {
                $this->bus->dispatchEvent(new Mira_Core_Event_UserEvent(Mira_Core_Event_UserEvent::VALIDATE, $this));
            }
            if ($this->pass_usr != $this->_initalPassword) {
                $this->bus->dispatchEvent(new Mira_Core_Event_UserEvent(Mira_Core_Event_UserEvent::PASSWORD_CHANGE, $this));
            };
        }
        
        $this->_initalPassword = $this->pass_usr;
        $this->_initalAccount = $this->account_status_usr;
        
        return $this->id_usr;
    }
    
    /**
     * Used for identification during serialization
     * 
     * @access private
     * @return string
     */
    public function getSerializableUid()
    {
        return self::getUID($this->id_usr);
    }
    
    /**
     * @access private
     * @param integer $id 
     * @return string
     */
    public static function getUID($id)
    {
        return "user|$id";
    }
    
    /**
     * Delete the user row and its vega contact
     */
    public function delete()
    {
        $vega = $this->api->selectVegas($this->api->selectVegaTypes()->where("fqn", "Mira_Core_Contact"))
                    ->where("id", $this->id_vg_usr)->fetchObject();
        $vega->fullDelete();
        return parent::delete();
        
    }
}
 