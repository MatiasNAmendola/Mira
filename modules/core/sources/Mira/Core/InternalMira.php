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
 * This class just makes sure nobody can login and set a default user on this API
 * That API is used throughout all our core VOs - so it has to be "stateless"
 * @author maz
 * 
 * @access private
 * @category   Mira
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_InternalMira extends Mira
{
    public function login($email = null, $password = null)
    {
        throw new Mira_Core_Exception_BadRequestException("Cannot change session. This is the core API system object. To have a custom API, create a new one (new Mira('your api key'))");
    }
    
    public function logout()
    {
        throw new Mira_Core_Exception_BadRequestException("Cannot change session. This is the core API system object. To have a custom API, create a new one (new Mira('your api key'))");
    }
}
?>