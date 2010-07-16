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
 * @see Mira_Utils_Event
 */
require_once 'Mira/Utils/Event.php';
/**
 * @see Mira_Utils_Event_CommandBus
 */
require_once 'Mira/Utils/Event/CommandBus.php';
/**
 * @see Mira_Core_select_Abstract
 */
require_once 'Mira/Core/Select/Abstract.php';
/**
 * @see Mira_Core_Db_Tables
 */
require_once 'Mira/Core/Db/Tables.php';
/**
 * @see Mira_Core_Event_VegaEvent
 */
require_once 'Mira/Core/Event/VegaEvent.php';
/**
 * @see Mira_Core_Event_LogEvent
 */
require_once 'Mira/Core/Event/LogEvent.php';

/**
 * Mira API main entry point
 * 
 * - configure: {@link init()}
 * - create new objects: {@link createVega()}, {@link createVegaType()}, {@link createUser()}
 * - retrieve existing objects: {@link selectVegas()}, {@link selectVegaTypes()}, {@link selectUsers()}
 * - session management: {@link login()}, {@link logout()}, {@link getUser()}
 * 
 * There are some magic functions you can use as shortcuts. For instance:
 * <code>
 * $api = new Mira();
 * 
 * // this
 * $vega = $api->vname("Paul");
 * // is equivalent to
 * $vega = $api->selectVegas()->where("name", "Paul")->fetchObject();
 * </code>
 * 
 * The general syntax for those functions is:
 * - first letter: "u" for user, "v" for vega or "t" for vegatype
 * - any word in between will be used as a filter (more generally you can put 
 *   a sequence of filters filter1Andfilter2Andfilter3 and provide as many args)
 * - OPTIONAL "_" to query multiple objects instead of only one {@link fetchAll()}
 *  
 * <code>
 * // this
 * $books = $api->vpublisherAndauthor_("best selling books", "Mary Higgins Clark");
 * // is equivalent to
 * $books = $api->selectVegas()
 * 				->where("publisher", "best selling books")
 * 				->where("author", "Mary Higgins Clark")
 * 				->fetchAll();
 * </code>
 * 
 * @category   Mira
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira
{
    const AUTHLEVEL_NOT_SET = 0;
    const AUTHLEVEL_APPLICATION = 1;
    const AUTHLEVEL_SYSTEM = 3;
    
    /**
     * @var Mira_Core_Auth
     */
    protected $auth;
    
    private $_apiKey;
    private $_authLevel = self::AUTHLEVEL_NOT_SET;
    
    /**
     * @var Zend_Db_Table
     */
    private $_vegaTable;
    /**
     * @var Zend_Db_Table
     */
    private $_vegaTypeTable;
    /**
     * @var Zend_Db_Table
     */
    private $_userTable;

    /**
     * @var Mira_Utils_Event_CommandBus
     */
    static protected $bus;
    
    static private $_initialized; 
    static private $_env; 
    static private $_defaultApiLevel = "system";
    
    /**
     * pass null to use the value defined in the config file (key = base.miralevel)
     * 
     * - With "system" $level you can do anything you want.
     * - With "application" $level you have open a session before using the API ({@link login()})
     * - With "locked" $level you cannot login (To be refined)
     * 
     * @param $level "system", "application", "locked" or null
     */
    public function __construct($level = null)
    {
        if (!self::$_initialized) {
            require_once 'Mira/Core/Exception/BadRequestException.php';
            throw new Mira_Core_Exception_BadRequestException("Mira was not initialized", "Call Mira::init before any API usage");    
        }
        
        if (!$level) $level = self::$_defaultApiLevel;
        $this->internalSetAPIKey($level);
        
        $this->_vegaTable = Mira_Core_Db_Tables::getInstance()->getVegaTable();
        $this->_vegaTypeTable = Mira_Core_Db_Tables::getInstance()->getVegaTypeTable();
        $this->_userTable = Mira_Core_Db_Tables::getInstance()->getUserTable();

        $this->auth = new Mira_Core_Auth($this);
    }

    /**
     * Mira uses .ini files to configure itself.
     * 
     * see an example in README.md
     * 
     * @param string $configFile path to .ini config file
     * @param string $env any string to describe your runtime config ("production", "test" or "development" for instance)
     */
    static public function init($configFile, $env = null)
    {
    	// setup autoloader
        require_once "Zend/Loader/Autoloader.php";
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);
        $autoloader->suppressNotFoundWarnings(true);
        // environment
        self::$_env = $env;
        // bus
        self::$bus = $bus = new Mira_Utils_Event_CommandBus();
        Zend_Registry::set(Mira_Core_Constants::REG_BUS, $bus);
        self::registerCommands($bus);
        // InitializationCommand
        $bus->dispatchNewEvent("initialize", array("configFile" => $configFile, "env" => $env));
        
        $conf = Zend_Registry::get(Mira_Core_Constants::REG_CONFIG);
        if (isset($conf->base->miralevel)) self::$_defaultApiLevel = $conf->base->miralevel; 
        self::$_initialized = true;

        // core api used by low level components
        // that one cannot be steteful, so its session functions are dsiabled
        $api = new Mira_Core_InternalMira("system");
        Zend_Registry::set(Mira_Core_Constants::REG_API, $api);
    }
    
    /**
     * Registers commands on the bus.
     * 
     * Mira uses internally the Command pattern for some tasks.
     * {@link Mira_Utils_Event_CommandBus}
     * 
     * You can override this function to define custom events you want to subscribe to.
     * 
     * @param Mira_Utils_Event_CommandBus $bus
     */
    static protected function registerCommands($bus)
    {
        $bus->registerCommand("*", "Mira_Core_Command_LogCommand", "logBusEvent"); 
        $bus->registerCommand("initialize", "Mira_Core_Command_InitializationCommand");  
    }
    
    /**
     * @return Mira_Utils_Event_CommandBus
     */
    public static function getBus()
    {
        return self::$bus;
    }
    
    /**
     * @return string
     */
    public static function getEnv()
    {
        return self::$_env;
    }
    
    // ###################################################
    // API SETTINGS / SECURITY
    // ###################################################
    
    /**
     * Someday we will implement an API key mechanism so that we can provide this API to 
     * third parties willing to extend our application.
     * 
     * @access private
     */
    private function internalSetAPIKey($level)
    {
        if ($level == "system")
            $authLevel = self::AUTHLEVEL_SYSTEM; 
        elseif ($level == "application")
            $authLevel = self::AUTHLEVEL_APPLICATION;
        else {
            $authLevel = self::AUTHLEVEL_NOT_SET;
        }
        
        $this->_authLevel = $authLevel;
        return $authLevel; 
    }

    public function getAuthLevel()
    {
        return $this->_authLevel;
    }
    
    // ###################################################
    // SESSION
    // ###################################################

    /**
     * @return boolean
     */
    public function isLoggued()
    {
        return $this->auth->isLoggued();
    }
    
    /**
     * Sets the global user of this session
     * 
     * @param string $email if null, then the user will be retrieved from the current session
     * @param string $password
     * @return boolean
     */
    public function login($email = null, $password = null)
    {
        if ($this->_authLevel > self::AUTHLEVEL_NOT_SET) {
            if ($email == "public@getvega.com") {
                throw new Mira_Core_Exception_BadRequestException("Cannot login as $email");
            }
        	self::$bus->dispatchEvent(new Mira_Core_Event_LogEvent("Login $email", Zend_Log::DEBUG));
            return $this->auth->login($email, $password);
        } else if (!$email) {
        	self::$bus->dispatchEvent(new Mira_Core_Event_LogEvent("Login from session", Zend_Log::DEBUG));
            return $this->auth->login();
        } else {
            throw new Mira_Core_Exception_BadRequestException("Your API key is not suitable for this operation (trying to relogin). Ask for a 'system' or 'application' API Key to your Vega administrator.");
        }
    }
    
    /**
     * Close this session
     * 
     * @param string $email if null, then the user will be retrieved from the current session
     * @param string $password
     */
    public function logout()
    {
        if ($this->_authLevel > self::AUTHLEVEL_NOT_SET) {
            $this->auth->logout();
        } else {
            throw new Mira_Core_Exception_BadRequestException("Your API key is not suitable for this operation (trying to logout). Ask for a 'system' or 'application' API Key to your Vega administrator.");
        }
    }
    
    /**
     * Set a user to be used by default in security checks. 
     * 
     * It can be different from the user really loggued in the PHP session.
     * 
     * @param Mira_Core_User $user
     */
    public function setUser($user)
    {
        if ($this->_authLevel > self::AUTHLEVEL_NOT_SET) {
            $this->auth->user = $user;
        } else {
            throw new Mira_Core_Exception_BadRequestException("Your API key is not suitable for this operation (trying to change session user). Ask for a 'system' or 'application' API Key to your Vega administrator.");
        }
    }
    
    /**
     * @return Mira_Core_User the current user registered with this API.
     */
    public function getUser()
    {
        return $this->auth->user;
    }
    
    /**
     * Sends an email with a token to change user's password
     * 
     * @param Mira_Core_User $user
     */
    public function sendRecoverPasswordEmail($user)
    {
        self::$bus->dispatchEvent(new Mira_Core_Event_UserEvent(Mira_Core_Event_UserEvent::CMD_RECOVER_PASSWORD, $user));
    }
    
    /**
     * Sends an email with a token to validate user's email
     * 
     * @param Mira_Core_User $user
     */
    public function sendValidationEmail($user)
    {
        self::$bus->dispatchEvent(new Mira_Core_Event_UserEvent(Mira_Core_Event_UserEvent::CMD_VALIDATE_EMAIL, $user));
    }
    
    // ###################################################
    // SELECTS
    // ###################################################
    
    /**
     * @param Mira_Core_VegaType | Mira_Core_Select_VegaTypeSelect | string | integer $vegaType 
     * @return VegaSelect
     */
    public function selectVegas($vegaType = null)
    {
        $sel = new Mira_Core_Select_VegaSelect($this, $this->_vegaTable);
        if (isset($vegaType)) {
            $sel->where("vegaType", $vegaType);
        }
        return $sel;
    }
    
    /**
     * @return VegaTypeSelect
     */
    public function selectVegaTypes()
    {
        $sel = new Mira_Core_Select_VegaTypeSelect($this, $this->_vegaTypeTable);
        return $sel;
    }
    
    /**
     * @return UserSelect
     */
    public function selectUsers()
    {
        $sel = new Mira_Core_Select_UserSelect($this, $this->_userTable);
        return $sel;
    }
    
    // ###################################################
    // CREATE
    // ###################################################
    
    /**
     * @return Mira_Core_Vega
     */
    public function createVega($type, $name = null, $owner = null)
    {
        if (Mira_Utils_String::isId($type)) $type = $this->tid($type);
        else if (is_string($type)) $type = $type->tname($type);
        if (!$type) throw new Mira_Core_Exception_BadRequestException("This VegaType does not exist or has not been saved.");
        
        $ret = $this->_vegaTable->createRowEx($type);
        $ret->setType($type);
        $ret->name = $name;
        if ($owner) $ret->owner = $owner;
        return $ret;
    }
    
    /**
     * @return Mira_Core_VegaType
     */
    public function createVegaType($name = null, $owner = null)
    {
        $ret  = $this->_vegaTypeTable->createRow();
        $ret->name = $name;
        $ret->owner = $owner;
        return $ret;
    }
    
    /**
     * @return Mira_Core_User
     */
    public function createUser($email, $password)
    {
        if ($this->uemail($email)) throw new Mira_Core_Exception_BadRequestException("User $email already exists");
        
        $ret  = $this->_userTable->createRow();
        $ret->email = $email;
        $ret->password = $password;
        return $ret;
    }
    
    // ###################################################
    // SHORTCUTS
    // ###################################################

    /**
     * Retrieve a vega by its id
     * @param integer $id
     * @return Mira_Core_Vega
     */
    public function vid($id) {return $this->__call("vid", array($id)); }
    /**
     * Retrieve a vega by its name
     * @param string $name
     * @return Mira_Core_Vega
     */
    public function vname($name) {return $this->__call("vname", array($name)); }
    /**
     * Retrieve a type by its id
     * @param integer $id
     * @return Mira_Core_VegaType
     */
    public function tid($id) {return $this->__call("tid", array($id)); }
    /**
     * Retrieve a type by its name
     * @param string $name
     * @return Mira_Core_VegaType
     */
    public function tname($name) {return $this->__call("tname", array($name)); }
    /**
     * Retrieve a user by its id
     * @param integer $id
     * @return Mira_Core_User
     */
    public function uid($id) {return $this->__call("uid", array($id)); }
    /**
     * Retrieve a user by its email
     * @param string $email
     * @return Mira_Core_User
     */
    public function uemail($email) {return $this->__call("uemail", array($email)); }
    
    /**
     * Turn magic function calls into non-magic function calls
     * 
     * <code>
     * $api->uid(3); 
     * // is equivalent to
     * $api->selectUsers()->where("id", 3)->fetchObject();
     * 
     * $api->vdirector("herve");
     * // is equivalent to
     * $api->selectVegas()->where("director", "herve")->fetchObject();
     * 
     * $api->tname_("Contact");
     * // is equivalent to
     * $api->selectVegaTypes()->where("name", "Contact")->fetchAll();
     * </code>
     *
     * @param string $method
     * @param array $args Zend_Db_Table_Select query modifier
     * @return Zend_Db_Table_Row_Abstract|Zend_Db_Table_Rowset_Abstract
     * @throws Zend_Db_Table_Row_Exception If an invalid method is called.
     */
    public function __call($method, array $args = array())
    {
        $matches = array();
        
        if (!count($args)) throw new Mira_Core_Exception_BadRequestException("Wrong argument count for method $method.");
        
        if (preg_match('/^(u|v|t)(\w+?)(?:and(\w+?))*(_)?$/', $method, $matches)) {
            if (!count($matches)) throw new Mira_Core_Exception_BadRequestException("Wrong argument count for method $method.");
            
            $sel = null;
            
            // SELECT
            $type = $matches[1];
            switch ($type) {
                case "u":    $sel = $this->selectUsers(); break;
                case "v":    $sel = $this->selectVegas(); break;
                case "t":    $sel = $this->selectVegaTypes(); break;
                default:     throw new Mira_Core_Exception_BadRequestException("Unrecognized method $method.");
            }
            
            // WHERES
            $all = $matches[count($matches)-1] == "_";
            for ($i = 2; $i <= ($all ? count($matches) - 2 : count($matches) - 1); $i++) {
                if (count($args) <= $i-2) throw new Mira_Core_Exception_BadRequestException("Wrong argument count for method $method.");
                $selector = strtolower($matches[$i]);
                $value = $args[$i - 2];
                $sel->where($selector, $value);
            }
            
            // FETCH TYPE
            if ($all) {
                return $sel->fetchAll();
            } else {
                return $sel->fetchObject();
            }
        }
        
        throw new Mira_Core_Exception_BadRequestException("Unrecognized method $method.");
    }
}

function print_vega($vega) 
{
    if ($vega && $vega instanceof Mira_Core_Vega) {
        print "\n\n$vega->name (" . $vega->type->name . ")";
        foreach ($vega->getVegaProperties() as $key => $value) {
            if ($value instanceof Mira_Core_Vega)
                print "\n. $key = $value->name";
            elseif (!is_object($value))
                print "\n. $key = $value";
        }
    } else {
        print "\nArgument is not a Mira_Core_Vega";
    }
}
