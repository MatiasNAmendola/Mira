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
 * @category   Mira
 * @package    Mira_Utils
 * @subpackage Event
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Utils_Event_EventDispatcher
{
    private $_eventName2listeners = array();
    
    public function __construct() 
    {
        $this->_eventName2listeners["*"] = array();
    }
    
    function addEventListener($eventName, $listener) 
    {
        if (is_string($listener) || is_array($listener) && count($listener) == 2) {
            $this->_eventName2listeners[$eventName][] = $listener;
        } else {
            throw new Exception("Wrong listener given");
        }
    }
    
    function removeEventListener($eventName, $listener)
    {
        $listeners = $this->_eventName2listeners[$eventName];
        if (!$listeners) return;
        foreach ($listeners as $key => $listener2) {
            if (is_string($listener2) && $listener2 === $listener) {
                unset($listeners[$key]);
            } elseif (is_array($listener2) && is_array($listener) && count($listener) == 2
                       && $listener[0] === $listener2[0]
                       && $listener[1] === $listener2[1]) {
                unset($listeners[$key]);
            }
        }
        $this->_eventName2listeners[$eventName] = $listeners;
    }
     
    function dispatchNewEvent($eventName, $data = null) 
    {
        $this->dispatchEvent(new Mira_Utils_Event($eventName, $data));
    }
    
    /**
     * 
     * @param Mira_Utils_Event $event
     */
    function dispatchEvent($event) 
    {
        if ($event instanceof Mira_Utils_Event) {
            $eventName = $event->name;
            $listeners = $this->_eventName2listeners["*"];
            if (isset($this->_eventName2listeners[$eventName])) {
                $listeners = array_merge($listeners, $this->_eventName2listeners[$eventName]);
            } 
            if (!$listeners) return;
            foreach ($listeners as $listener) {
                call_user_func_array($listener, array($event));
            }
        } else {
            throw new Exception("Wrong argument given. Expected a Mira_Utils_Event");
        }
    }
}