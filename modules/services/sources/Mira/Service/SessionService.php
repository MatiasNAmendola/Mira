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
 * @package    Mira_Service
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @category   Mira
 * @package    Mira_Service
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Service_SessionService extends Mira_Service_Abstract
{
    public $fullApi;
    
    public function __construct()
    {
        parent::__construct();
        // we need special priviledges in that service 
        // (login / logout)
        $this->fullApi = new Mira("application");
    }
    
    /**
     * Simple endpoint to check connection settings.
     */
    public function testConnection () 
    {
        return "success";
    }
    
    /**
     * Exceptions have to pass serializations
     * 
     * Otherwise you can get cryptic errors...
     * 
     * @param integer $code
     */
    public function testExceptions ($code)
    {
        switch ($code) {
            case 400: throw new Mira_Core_Exception_BadRequestException("test exception $code");
            case 401: throw new Mira_Core_Exception_NotAuthorizedException($this->api->getUser(), "test exception $code");
            case 404: throw new Mira_Core_Exception_NotFoundException("test exception $code");
            default:  throw new Mira_Core_Exception_NotFoundException("Exception $code");
        }
    }
    
    /**
     * User login
     * 
     * Simple login that checks if the user is registered AND validated
     * It opens a PHP session ({@link Zend_Auth})
     * 
     * @param string $email
     * @param string $password 
     * @return Mira_Core_User
     */
    public function login ($email, $password)
    {
        if ($this->fullApi->login($email, $password)) {
            return $this->fullApi->getUser();
        } else {
            throw new Mira_Core_Exception_BadRequestException("Wrong email/password password combination. Could not login.");
        }
    }
    

    /**
     * User logout
     */
    public function logout ()
    {
        $this->fullApi->logout();
    }
}
