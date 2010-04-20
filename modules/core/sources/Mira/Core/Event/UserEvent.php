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
 * @subpackage Event
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * see {@link Mira_Core_Command_UserCommand}
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Event
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Event_UserEvent extends Mira_Utils_Event
{
    const EDIT = "user_edit";
    const VALIDATE = "user_validate";
    const PASSWORD_CHANGE = "user_password_change";
    const CREATE = "user_create";
    const DELETE = "user_delete";
    
    // events that are bound directly on commands
    const CMD_VALIDATE_EMAIL = "user_validate_email"; 
    const CMD_RECOVER_PASSWORD = "user_recover_password"; 
    
    /** @var Mira_Core_User */
    public $user;
    
    public $related;
    
    public function __construct($name, $user, $related = null)
    {
        parent::__construct($name, null);
        $this->user = $user;
        $this->related = $related;    
    }
}
?>