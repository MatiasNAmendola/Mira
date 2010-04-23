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
 * @subpackage Exception
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @see Mira_Core_Exception
 */
require_once "Mira/Core/Exception.php";

/**
 * Whenever RBAC fails.
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Exception
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Exception_NotAuthorizedException extends Mira_Core_Exception
{
    const CODE = 401;
    
    public $user;
    public $action;
    
    public function __construct($user = null, $action = null)
    {
        $this->user = $user;
        $this->action = $action;
        parent::__construct($this->buildMessage(), self::CODE);
    }
    
    protected function buildMessage()
    {
        return "User " . ($this->user ? $this->user->email : "") . 
        	" cannot " . ($this->action ? $this->action : "perform this operation");
    }
}