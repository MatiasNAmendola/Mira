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
 * We chose to use our Event framework even to log messages as it completely decouples
 * our code from Zend_Log (the logging framework we use underneath).
 * Sample usage to log a warning:
 * <code>
 * $bus = Mira::getBus();
 * $bus->dispatchEvent(new Mira_Core_Event_LogEvent("Warning!!! System under attack!", "warn"));
 * </code>
 *  
 * For more info on our Event framework, have a look at {@link Mira_Utils_Event_CommandBus}.
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Command
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Command_LogCommand extends Mira_Utils_Event_AbstractCommand
{
    /** 
     * @var Zend_Log 
     */
    public $logger;
    
    public function __construct() 
    {
        try {
            $this->logger = Zend_Registry::get(Mira_Core_Constants::REG_LOG);    
        } catch (Zend_Exception $e) {
            // Mira is not yet initialized
        }
    }
    
    public function log($event)
    {
        if (!$this->logger) return;
        
        if ($event instanceof Mira_Core_Event_LogEvent) {
            $lvl = $event->level;
            if (is_string($lvl)) $lvl = self::convertStringLevel($lvl);
            $this->logger->log($event->message,$lvl);
        } else {
            $this->logger->log("Wrong event type specified. Expected Mira_Core_Event_LogEvent got $event", Zend_Log::WARN);
        }
    }
    
    public function logBusEvent($event) 
    {
        if (!$this->logger) return;
        if ($event->name == "log") return;
        $this->logger->log("Bus event: " . $event->name, Zend_Log::DEBUG);
    }
    
    /**
     * @access private
     */
    static private function convertStringLevel($stringLevel)
    {
        $stringLevel = strtolower($stringLevel);
        switch ($stringLevel) {
            case "emerg": return Zend_Log::EMERG;
            case "alert": return Zend_Log::ALERT;
            case "crit": return Zend_Log::CRIT;
            case "err": return Zend_Log::ERR;
            case "warn": return Zend_Log::WARN;
            case "notice": return Zend_Log::NOTICE;
            case "info": return Zend_Log::INFO;
            case "debug": return Zend_Log::DEBUG;
        }
        return Zend_Log::DEBUG;
    }
}
