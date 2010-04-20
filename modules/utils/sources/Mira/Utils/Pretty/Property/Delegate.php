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
 * @subpackage Pretty
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @category   Mira
 * @package    Mira_Utils
 * @subpackage Pretty
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Utils_Pretty_Property_Delegate extends Mira_Utils_Pretty_Property_Abstract 
{
    // @var string
	public $delegate;
	// @var object 
	public $settings; 
	
	public function __construct($target, $prettyName, $useCache = false,  $autocommit = true, $delegate, $settings = null, $transient = false) 
	{
		parent::__construct($target, $prettyName, $useCache, $autocommit, $transient);
		$this->delegate = $delegate;
	    $this->settings = $settings; 
	}
	
	public function getValue() 
	{
	    $funcName = "get" . ucfirst($this->delegate);
	    if (isset($this->settings))
		    return $this->target->$funcName($this->settings);
		else
		    return $this->target->$funcName();
	}
	
	public function setValue($value) 
	{
	    $funcName = "set" . ucfirst($this->delegate);
	    if (isset($this->settings))
		    return $this->target->$funcName($this->settings, $value);
		else
		    return $this->target->$funcName($value);
	}
}
