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
 * @subpackage Command
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * {@link Mira::init()} launches this command. It is the central place for all initialization
 * stuff (database, logs, email...).
 * 
 * For more info on our Event Framework, have a look at {@link Mira_Utils_Event_CommandBus}.
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Command
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Command_InitializationCommand extends Mira_Utils_Event_AbstractCommand
{
    private $_configFile;
    private $_env;
    
    private $_config;
    
    // COMMAND FUNCTIONS
    
    public function run($action, $event) 
    {
        $this->_configFile = $event->data["configFile"];
        $this->_env = $event->data["env"];
        
        $this->loadConfig();
        
        $this->initDatabase();
        $this->initMail();
        $this->initLogs();
    }

    // INTERNALS
    
    /**
     * @access private
     */
    private function loadConfig()
    {
        $this->_config = new Zend_Config_Ini($this->_configFile, $this->_env);
        Zend_Registry::set(Mira_Core_Constants::REG_CONFIG, $this->_config);

        if (isset($this->_config->base->timezone))
            $timezone = $this->_config->base->timezone;
        else 
            $timezone = "Europe/Paris";
        date_default_timezone_set($timezone);
    }
    
    // INIT FUNCTIONS
    
    /**
     * @access private
     */
    private function initDatabase()
    {
        $dbAdapter = Zend_Db::factory($this->_config->database);
        $dbAdapter->setFetchMode(Zend_Db::FETCH_OBJ);
        Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);
        Zend_Registry::set(Mira_Core_Constants::REG_DBADAPTER, $dbAdapter);
        
        // are our tables created ?
        try {
            $sel = $dbAdapter->select()->from(Mira_Core_Constants::TABLE_VEGATYPE)->limit(1);
            $dbAdapter->fetchOne($sel);
        } catch (Exception $e) {
            $this->createMiraDatabase($dbAdapter);
        }
    }
    
    /**
     * @access private
     */
    private function createMiraDatabase($dbAdapter) 
    {
        $sqlDump = dirname(__FILE__) . "/../Db/dump.sql";
        $file_handle = fopen($sqlDump, "r");
        $string = '';
        while (! feof($file_handle)) {
            $string = $string . fgetss($file_handle);
        }
        fclose($file_handle);
        
        try {
            $dbAdapter->query($string);
        } catch (Zend_Exception $e) {
            $dbConfig = $dbAdapter->getConfig();
            if (isset($dbConfig["dbname"])) {
                $dbName = $dbConfig["dbname"]; 
                throw new Mira_Core_Exception("Error creating Mira database. Have you created the db $dbName ?");
            } else {
                throw new Mira_Core_Exception("Error creating Mira database. Please check db settings inside $this->_configFile");
            }
        }
    }
    
    /**
     * @access private
     */
    private function initMail()
    {
        if (isset($this->_config->mail->smtp)) {
            $params = $this->_config->mail->smtp;
            $smtp = $params->toArray();
            $mailTransport = new Zend_Mail_Transport_Smtp($smtp['smtp'],$smtp['params']);
            Zend_Mail::setDefaultTransport($mailTransport);
        }
    }
    
    /**
     * @access private
     */
    private function initLogs()
    {
        if (isset($this->_config->log)) {
            $params = $this->_config->log;
            $params = $params->toArray();
            $log = Zend_Log::factory($params);
            Zend_Registry::set(Mira_Core_Constants::REG_LOG, $log);
        }
    }
}
