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
 * @package    Mira_Utils
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @category   Mira
 * @package    Mira_Utils
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Utils_PasswordEncryption
{
    public static function generateSalt ($email)
    {
        $salt = sha1("--" . 'myCrypt' . "--" . $email . "--");
        
        return ($salt);
    }

    public static function encrypt ($password, $salt)
    {
        $cryptedPassword = sha1("--" . $salt . "--" . $password . "--");
        
        return ($cryptedPassword);
    }

    public static function encryptPassword ($data)
    {
        if (empty($data['salt_usr'])) {
            $data['salt_usr'] = self::generateSalt($data['email_usr']);
        }
        
        if (! empty($data['pass_usr'])) {
            $data['pass_usr'] = self::encrypt($data['pass_usr'], $data['salt_usr']);
        }
        
        return ($data);
    }
}