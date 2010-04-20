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
class Mira_Utils_Pretty_Property_Basic extends Mira_Utils_Pretty_Property_Abstract 
{
	protected $value;
	
	protected $targetProperty;
	
	/**
	 * @param string $prettyName
	 * @param boolean $useCache
	 * @param unknown_type $value
	 * @param string $targetProperty
	 */
	public function __construct($target, $prettyName, $useCache = false, $autocommit = true, $value = null, $targetProperty = null) 
	{
		parent::__construct($target, $prettyName, $useCache, $autocommit);
		
		if ($value == null && $targetProperty == null) {
			throw new Exception("value or targetProperty were not set");
		}
		
		$this->value = $value;
		$this->targetProperty = $targetProperty;
	}
	
	public function getValue() 
	{
		if (isset($this->value)) 
			return $this->value;
		else {
			return $this->target[$this->targetProperty];
		}
	}

    public function setValue ($value)
    {
        if (isset($this->value)) 
            $this->value = $value;
        else 
            $this->target[$this->targetProperty] = $value;
    }
}
