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
class Mira_Utils_Pretty_Helper 
{
	protected $target;
	protected $propertiesByName;
	
	/**
	 * @param array $properties
	 * @param unknown_type $target
	 */
	public function __construct($properties, $target) 
	{
		$this->propertiesByName = array();
		if (is_array($properties) && isset($target)) {
			$this->target = $target;	
			$this->addPrettyProperties($properties);
		} else {
			throw new Exception("properties not an array, or target was not set.");
		}
	}
	
	/**
	 * @param array $properties
	 */
	public function addPrettyProperties($properties)
	{
		foreach ($properties as $property) {
			$this->propertiesByName[$property->prettyName] = $property;
		}
	}
	
	/**
	 * @param Mira_Utils_Pretty_Property_Abstract $property
	 */
	public function addPrettyProperty($property)
	{
		$this->propertiesByName[$property->prettyName] = $property;	
	} 
	
	/**
	 * @param string $name
	 */
	public function removePrettyProperty($name)
	{
	    unset($this->propertiesByName[$name]);
	}
	
	public function getValue($name) 
	{
		if (isset($this->propertiesByName[$name])) {
			return $this->propertiesByName[$name]->internalGetValue();
		} else {
			throw new Exception("Property $name does not exist.");
		}
	} 
	
	public function setValue($name, $value) 
	{
		if (isset($this->propertiesByName[$name])) {
    		$property = $this->propertiesByName[$name];
			return $property->internalSetValue($value);
		} else {
			throw new Exception("Property $name does not exist.");
		}
	}
	
	/**
	 * @return IteratorAggregate
	 */
	public function getPropertiesIterator()
	{
	    $arrayWithoutValues = array();
	    foreach ($this->propertiesByName as $key=>$value) {
	        if (!$value->transient)
                $arrayWithoutValues[$key] = $this->getValue($key);      
	    }
        return new ArrayIterator($arrayWithoutValues);
	}
	
	/**
	 * @return array
	 */
	public function getProperties()
	{
	    return array_values($this->propertiesByName);
	}
}
