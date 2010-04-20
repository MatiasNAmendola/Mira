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
class Mira_Utils_Pretty_Row extends Zend_Db_Table_Row implements IteratorAggregate
{

    protected $prettyHelper;

    public function __construct ($config, $properties)
    {
        $this->prettyHelper = new Mira_Utils_Pretty_Helper($properties, $this);
        parent::__construct($config);
    }

    public function __get ($name)
    {
        try {
            return $this->prettyHelper->getValue($name);
        } catch (Exception $e) {
            try {
                return parent::__get($name);
            } catch (Exception $e) {
                return null;
            }
        } 
    }

    public function __set ($name, $value)
    {
        try {
            return $this->prettyHelper->setValue($name, $value);
        } catch (Exception $e) {
            return parent::__set($name, $value);
        }
    }
    
    public function getIterator()
    {
        return $this->prettyHelper->getPropertiesIterator();
    }
    
	public function addPrettyProperty($property)
	{
	    $this->prettyHelper->addPrettyProperty($property);
	}
    
	public function addPrettyProperties($properties)
	{
	    $this->prettyHelper->addPrettyProperties($properties);
	}
    
	public function removePrettyProperty($name)
	{
	    $this->prettyHelper->removePrettyProperty($name);
	}
}
