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
 * @subpackage Event
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @see Mira_Utils_Event_EventDispatcher 
 */
require_once "Mira/Utils/Event/EventDispatcher.php";

/**
 * This is an implementation of {@link http://en.wikipedia.org/wiki/Command_pattern}
 * 
 * At the very core of it there is a bus of events - you can dispatch any kind of {@link Mira_Utils_Event}
 * on it, and other actors can subscribe to those events ({@link Mira_Utils_Event_EventDispatcher::addEventListener}) or 
 * register a {@link Mira_Utils_Event_AbstractCommand} to be ran.
 * 
 * @category   Mira
 * @package    Mira_Utils
 * @subpackage Event
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Utils_Event_CommandBus extends Mira_Utils_Event_EventDispatcher
{
    protected $registeredCommands = array();
    
    public function __construct ()
    {
        parent::__construct();
        $this->registeredCommands["*"] = array();
    }
    
    public function registerCommand($eventName, $commandCls, $commandAction = null)
    {
        if (!isset($this->registeredCommands[$eventName])) {
            $this->registeredCommands[$eventName] = array();
        }
        $this->registeredCommands[$eventName][] = array("cls" => $commandCls, "action" => $commandAction);
    }
    
    public function unregisterCommand($eventName, $commandCls, $commandAction = null)
    {
        
        if (isset($this->registeredCommands[$eventName])) {
            $cmds = $this->registeredCommands[$eventName];
            foreach ($cmds as $key => $cmd) {
                if ($cmd["cls"] == $commandCls && ($commandAction == "*" || $cmd["action"] == $commandAction)) {
                    unset ($cmds[$key]);
                }
            }
            $this->registeredCommands[$eventName] = $cmds;
        } else {
            throw new Exception("Command $commandCls was not registered.");            
        }
    }
    
    /**
     * 
     * @param Mira_Utils_Event $event
     */
    function dispatchEvent($event) 
    {
        // run registered commands
        if ($event instanceof Mira_Utils_Event) {
            $eventName = $event->name;
            $cmds = $this->registeredCommands["*"];
            if (isset($this->registeredCommands[$eventName])) {
                $cmds = array_merge($cmds, $this->registeredCommands[$eventName]);
            } 
            foreach ($cmds as $cmd) {
                $cls = $cmd["cls"];
                $action = $cmd["action"];
                Zend_Loader::loadClass($cls);
                /** Mira_Utils_Event_AbstractCommand */
                $cmdInstance = new $cls();
                $cmdInstance->internalRun($action, $event);
            }
        } else {
            throw new Exception("Wrong argument given. Expected a Mira_Utils_Event");
        }
        
        // notify other observers
        parent::dispatchEvent($event);
    }
}