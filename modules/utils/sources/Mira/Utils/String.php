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
class Mira_Utils_String {

	/**
	 * Escapes the given string to be url compliant.
	 * @param $value
	 * @return string
	 */
	public static function escape ($value)
    {
    	return ereg_replace("[^A-Za-z0-9_]", "", $value);
    }  
     
    /**
     * 
     * @param boolean $value
     */
    public static function isId($value)
    {
        if ($value === "1") return true;
        else if (is_object($value)) return false;
        else if (strval($value) != 0) return true;
        return false;
    }
    
    /**
     * @param string $value
     * @return true if null or equal to ""
     */
    public static function isEmpty($value)
    {
        return !$value || $value == "";    
    }

    public static function fqnize($value)
    {
        return $value ? str_replace(" ", "", strtolower($value)) : "empty";
    }
    
    public static function randomString($length = 10, $chars = '1234567890') {
    
        // Alpha lowercase
        if ($chars == 'alphalower') {
            $chars = 'abcdefghijklmnopqrstuvwxyz';
        }
    
        // Numeric
        if ($chars == 'numeric') {
            $chars = '1234567890';
        }
    
        // Alpha Numeric
        if ($chars == 'alphanumeric') {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        }
    
        // Hex
        if ($chars == 'hex') {
            $chars = 'ABCDEF1234567890';
        }
    
        $charLength = strlen($chars)-1;
    
        $randomString = "";
        for($i = 0 ; $i < $length ; $i++) {
            $randomString .= $chars[mt_rand(0,$charLength)];
        }
    
        return $randomString;
    }
}
