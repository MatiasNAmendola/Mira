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
 * Describes a property of a vegatype.
 * 
 * @category   Mira
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Property extends Mira_Utils_Pretty_Row
{
 
    /**
     * this is used by AMF to specify remote AS classname
     * @access private
     * @var string
     */
    public $_explicitType = "com.vega.core.api.vega.VegaProperty";
    
    /**
     * @var Mira_Core_VegaType
     */
    public $propertyOwner;

	// @var Mira
	private $api;
    
 	public function __construct($config)
 	{
 	    // this table exposed properties
 	    $properties = array();
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "id", false, true, "baseProperty", "id_prp");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "name", false, true, "baseProperty", "name_prp");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "type", false, true, "type");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "position", false, true, "baseProperty", "position_prp");

		// API
		$this->api = Zend_Registry::get(Mira_Core_Constants::REG_API); 
 	    
 	    parent::__construct($config, $properties);
 	}
 	
 	public function isDirty()
 	{
 	    $mf = $this->_modifiedFields;
 	    foreach ($mf as $field => $isDirty) {
 	        if ($isDirty) return true;
 	    }
 	    return false;
 	}
 	
 	 /**
     * Sets type
     * 
     * @param integer | Mira_Core_VegaType id of the primitive or vegatype's VO
     */
 	public function setType($value)
 	{
 	    if ($this->id_prp) {
 	        throw new Mira_Core_Exception_BadRequestException("Cannot change type of a property yet when it has already been saved");
 	    } else {
 	        if ($value instanceof Mira_Core_VegaType) {
 	            $this->type_id_vgt_prp = $value->id;
 	        } else {
 	            $prm = null;
 	            if ($value instanceof Mira_Core_Primitive) {
                    $prm = $value;
 	            } elseif (Mira_Utils_String::isId($value)) {
 	                $app = Zend_Registry::get(Mira_Core_Constants::REG_PRIM_APPLICATION);
 	        	    $prm = $app->findById($value);
 	            } else {
 	                $app = Zend_Registry::get(Mira_Core_Constants::REG_PRIM_APPLICATION);
 	        	    $prm = $app->findByName($value);
 	            }
 	        	if ($prm) {
 	                $this->type_id_prm_prp = $prm->id;
 	        	} else {
 	        		throw new Mira_Core_Exception_BadRequestException("Property type not recognized $value");
 	        	}
 	        }
 	    }
 	}
 	
 	 /**
     * Get Type Value
     * 
     * @return Mira_Core_VegaType
     */
 	public function getType()
 	{
 	    if ($this->isPrimitive()) {
            return $this->type_id_prm_prp;
        } else {
            return Zend_Registry::get(Mira_Core_Constants::REG_API)->tid($this->type_id_vgt_prp);
        }
 	}

 	/**
     * internal
     * 
     * @param string $localName 
     * @param mixed $value 
     */
    public function setBaseProperty($localName, $value)
    {
        if ($localName == "id_prp") {
            throw new Mira_Core_Exception_BadRequestException("Cannot change property id");
        }
        $this->$localName = $value;
    }
    
    /**
     * internal
     * 
     * @param string $localName 
     * @return mixed
     */
    public function getBaseProperty($localName)
    {
        return parent::__get($localName);
    }
 	
    /**
     * @return boolean
     */
    public function isPrimitive ()
    {
        return ($this->type_id_vgt_prp == NULL);
    }
    
    /**
     * Get the Sql Type of this property
     * or null if it is not primitive
     * 
	 * @param integer $propertyId
     * @return string
     */
    public function getSQLType () 
    {
        if (!$this->isPrimitive())
            return null;
             
        switch ($this->getType()) {
            case 1: return "varchar (200)";
            case 2: return "decimal";
            case 3: return "varchar (100)";
            case 4: return "text";
            case 5: return "tinyint (1)";
            case 6: return "varchar (2000)"; // we can have long urls
        }
        return "text";
    }
    
    /**
     * Get the sql column name of this property
     * 
     * @return integer
     */
    public function getColumnName ()
    {
        if ($this->isPrimitive())
            return $this->id_prp;
        else
            throw new Exception("Property is a linked vega. column name is undefined");
    }
    
    /**
     * maybe we should forbid this way of saving properies.
     * the normal way is to pass by the vegatype add / remove / edited methods
     * @return integer
     * 
     */
    public function save()
    {
    	if ($this->id_vgt_prp) {
    	    if (!$this->type_id_vgt_prp && !$this->type_id_prm_prp) {
                $this->type_id_prm_prp = 1;
    	    }
            parent::save();
    	}
    	else {
    		throw new Exception("Property doesn't have a vegatype");
    	}
    }
    
    /**
     * @return Mira_Core_Property unsaved
     */
    public function duplicate()
    {
        $ret = $this->getTable()->createRow();
        $ret->id_vgt_prp = $this->id_vgt_prp;
        $ret->id_prp = $this->id_prp;
        $ret->name_prp = $this->name_prp;
        $ret->type_id_prm_prp = $this->type_id_prm_prp;
        $ret->type_id_vgt_prp = $this->type_id_vgt_prp;
        $ret->position_prp = $this->position_prp;
        $ret->propertyOwner = $this->propertyOwner;
        return $ret;
    }
}