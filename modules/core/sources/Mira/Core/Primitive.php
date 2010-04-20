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
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * Primitives are just literal values as opposed to objects. For instance 
 * Mira_Core_Contact defines a phone number property which is a litteral.
 * (VegaTypes can define "vega" or "primitive" properties)
 * 
 * @category   Mira
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Primitive extends Mira_Utils_Pretty_Row
{
    /**
     * this is used by AMF to specify remote AS classname
     * @access private
     * @var string
     */
    public $_explicitType = "com.vega.core.api.vega.VegaPrimitive";
    
 	public function __construct($config)
 	{
 	    // this table exposed properties
 	    $properties = array();
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "id", false, true, "baseProperty", "id_prm");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "name", false, true, "baseProperty", "name_prm");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "sqltype", false, true, "baseProperty", "sqltype_prm");
 	    
 	    parent::__construct($config, $properties);
 	}

   /**
     * internal
     * 
     * You can retrieve properties simply by doing :
     * <code>
     * $id = $primitive->id;
     * $name = $primitive->name;
     * $type = $primitive->sqlType;
     * </code>
     * 
     * @access private
     * @param string $localName
     * @return mixed
     */
    public function getBaseProperty($localName)
    {
        return parent::__get($localName);
    }
 	    
    /**
     * @return integer 
     */
    public function save()
    {
        parent::save();
    }
    
}