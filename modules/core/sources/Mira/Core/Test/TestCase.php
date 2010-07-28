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
 * @subpackage Test
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @see Zend_Registry
 */
require_once "Zend/Registry.php";
/**
 * @see PHPUnit_Framework_TestCase
 */
require_once "PHPUnit/Framework/TestCase.php";
/**
 * @see Mira
 */
require_once "Mira.php";

/**
 * A utility TestCase that cleans its tables before running
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Test
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Test_TestCase extends PHPUnit_Framework_TestCase
{
    protected static $sqldump;
    protected static $config;
    protected static $doResetDb = true;
    
    public static function setUpBeforeClass()
    {
        if (!self::$sqldump) self::$sqldump = dirname(__FILE__) . "/../Db/dump.sql";
        if (!self::$config) self::$config = "config.ini";
        self::initalizeTests(self::$sqldump, self::$config, self::$doResetDb);
    }
    
    public static function initalizeTests($sqldump, $config, $doResetDb)
    {
        if (!Zend_Registry::isRegistered('testInitialized')) {
            
            define("MIRA_ROOT", dirname(__FILE__) . "/../../../../../../");
            
            // instantiate Mira
            Mira::init($config, "test");
            
            // clean database
            if ($doResetDb) {
                $dbAdapter = Zend_Registry::get(Mira_Core_Constants::REG_DBADAPTER); 
                $dbCleaner = new Mira_Utils_DatabaseCleaningHelper();
                $dbCleaner->clean($dbAdapter, $sqldump);
            }
            
            Zend_Registry::set('testInitialized', true);
        }
    }
}
