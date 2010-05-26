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
 * @see Mira_Utils_Pretty_Row
 */
require_once "Mira/Utils/Pretty/Row.php";

/**
 * @see Mira_Utils_IVersionable
 */
require_once "Mira/Utils/IVersionable.php";

/**
 * Equivalent to an "Instance" in OOP or a "Resource" in RDF
 * 
 * See README.md for more info.
 * 
 * @category   Mira
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Vega extends Mira_Utils_Pretty_Row implements Mira_Utils_IVersionable
{

    /**
     * this is used by AMF to specify remote AS classname
     * @access private
     * @var string
     */
    public $_explicitType = "com.vega.core.api.vega.Vega";
    
    // @var integer
    public $thisId;

	// @var Mira
	protected $api;

	// @var Mira_Utils_Event_CommandBus
	protected $bus;
    
    // whatever the vegatype version of the current vega is
    // the table name of the extension remains the same
    private $_extensionTableName;
    
    private $_id2vegaProperty;
    
    // objects containing current properties
    // @var integer
    private $_currentRevision;
    // @var Zend_Db_Table_Row
    private $_currentRow;
    // @var Zend_Db_Table_Row
    private $_currentExtensionRow;
    // @var Mira_Core_VegaType
    private $_currentType;
    // @var Mira_Core_Scope
    private $_currentScope;

    // buffer for current modifications
    private $_nextRow;
    
    /**
     * This should not be instantiated directly. Use {@link Mira::selectVegas()} or {@link Mira::createVega()}
     * 
     * A Mira_Core_Vega is the root object of all Mira object database. It supports versionning and security.
     * Read README.md for more information.
     * 
     * It exposes those properties (you won't have them by autocomplete / intellisense / phpdoc
     * as they are accessed by magic php functions) :
     *   - id
     *   - revision 0 = new / unsaved, 1 = first revision
     *   - name
     *   - creationDate
     *   - status : enabled, disabled (not the latest version) or deleted (in trash)
     *   - type : Mira_Core_VegaType describing this object
     *   - owner : Mira_Core_User
     *   - scope : RBAC security definition of this vega
     *   - vegaProperties : associative array prop name => value
     *   - + all custom properties of this vega
     * 
     * @param $config
     */
 	public function __construct($config)
 	{
 	    $properties = array();
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "id", false, true, "baseProperty", "id_vg");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "name", false, true, "baseProperty", "name_vg");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "creationDate", false, true, "baseProperty", "date_created_vg");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "status", false, true, "baseProperty", "status_vg");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "revision", false, true, "baseProperty", "rv_vg");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "type", false, true, "type");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "owner", false, true, "owner");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "scope", false, true, "scope");
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "vegaProperties", false, true, "vegaProperties");
 	    // transient
 	    $properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "_serializableUid", false, true, "serializableUid", null, true);

		// API
		$this->api = Zend_Registry::get(Mira_Core_Constants::REG_API); 
		// BUS
		$this->bus = Zend_Registry::get(Mira_Core_Constants::REG_BUS); 
		
 	    parent::__construct($config, $properties);
 	}
 	
    /**
	 * init() function is called after a Table->createNew() or a Table->fetchRow() is called
	 * we have here to instantiate the whole structure, including the extensionTable
	 * @access private
     */
    public function init ()
    {
        parent::init();
        $rev = $this->rv_vg;
        if (!isset($rev)) {
            $rev = 0;
        } else {
            $this->thisId = $this->id_vg;
        }
        $this->moveToRevision($rev);
    }

    /**
     * @access private
     * @param $name
     * @param $value
     */
    public function __set ($name, $value)
    {
        try {
            return parent::__set($name, $value);
        } catch (Exception $e) {
            throw new Mira_Core_Exception_NotFoundException("Property $name in " . $this->_currentType->name);
        }
    }
    
    /**
     * Does this vega need to be saved
     * 
     * @return boolean
     */
    public function isDirty()
    {
        // check scope
        if ($this->_currentScope && $this->_currentScope->isDirty()) {
            $this->scopeChanged();
            return true;    
        }
        // check others
        $nr = $this->_nextRow;
        $modifNb = count($nr["base"]) + count($nr["extension"]);
        if ($modifNb == 0 && !$nr["scopeChanged"]) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * @return boolean
     */
    public function isBrandNew()
    {
        return !isset($this->thisId) && (!isset($this->rv_vg) || $this->rv_vg == 0);
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return integer
     */
    public function getRevisionNumber() 
    {
        return $this->_currentRevision;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @param integer $revision 
     * @return integer
     */
    public function moveToRevision($revision)
    {
        if (!($this->id_vgt_vg)) {

            // we can't do anything in such case
            return;
        
        } else {
            
            $latestRv = $this->getLatestRevisionNumber();
            
            // A - prepare the vegatype
            // ------------------------
            // little improvement possible : if the vegatype has not changed, 
            // we don't need to reset this
            if ($latestRv == $revision) {
                // we pick the latest type revision
                $this->_currentType = Zend_Registry::get(Mira_Core_Constants::REG_API)->tid($this->id_vgt_vg);
            } else {
                // we pick the type revision specified by rv_vgt_vg
                $sel = Zend_Registry::get(Mira_Core_Constants::REG_API)->selectVegaTypes()
                                                ->where("id", $this->id_vgt_vg)
                                                ->where("revision", $this->rv_vgt_vg);
                $this->_currentType = $sel->fetchObject();
            }
            
            $this->_extensionTableName = $this->_currentType->getTableName();
            
            // we remove vega properties set from last revision
            if (isset($this->_id2vegaProperty)) {
                foreach ($this->_id2vegaProperty as $prpId => $vegaProperty) {
                    $this->removePrettyProperty($vegaProperty->name_prp);
                } 
            }
            // now add the new ones
            $this->_id2vegaProperty = array();
            $vegaProperties = $this->_currentType->getVegaProperties();
            foreach ($vegaProperties as $vegaProperty){
                $this->_id2vegaProperty[$vegaProperty->id_prp] = $vegaProperty;
                $propertyName = $vegaProperty->name_prp;
                $this->addPrettyProperty(new Mira_Utils_Pretty_Property_Delegate($this, $propertyName, false, true, "vegaProperty", $vegaProperty));
            }
            
            $this->_currentRevision = $revision;
            
            if ($revision == 0) {
                // is brand new
                // we don't have to prepare anything everything
                // will go in the new row buffers
            } else {
                
            // B - prepare the vega
            // --------------------
                // current
                $db = $this->getTable()->getAdapter();
                // 2. base table
                $select = $db->select();
                $select->from(Mira_Core_Constants::TABLE_VEGA)
                       ->where("id_vg = ".$this->thisId)
                       ->where("rv_vg = ". $this->_currentRevision);
                $this->_currentRow = $db->fetchRow($select);
                // 3. extension table
                $select = $db->select();
                $select->from($this->_extensionTableName)
                       ->where("id_vg = ".$this->thisId)
                       ->where("rv_vg = ". $this->_currentRevision);
                $this->_currentExtensionRow = $db->fetchRow($select);

                // we pick the scope revision specified by rv_scp_vg
                $this->_currentScope = $this->retrieveScope($this->_currentRow->id_scp_vg);
            }
        
            
            // next
            $this->_nextRow = array(
                "base" => array(), 
                "extension" => array(),
                "scopeChanged" => false);
        }
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return boolean
     */
    public function hasNext()
    {
        return $this->_currentRevision < $this->getLatestRevisionNumber();
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return boolean
     */
    public function hasPrevious()
    {
        return $this->_currentRevision > 1;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return Mira_Core_Vega
     */
    public function nextRevision()
    {
        if ($this->hasNext()) {
            $this->moveToRevision($this->_currentRevision + 1);
        }
        return $this;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return Mira_Core_Vega
     */
    public function previousRevision()
    {
        if ($this->hasPrevious()) {
            $this->moveToRevision($this->_currentRevision - 1);
        }
        return $this;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return Mira_Core_Vega
     */
    public function firstRevision()
    {
        $this->moveToRevision(1);
        return $this;
    }
    
    /**
     * {@inheritdoc}
	 *
     * @return Mira_Core_Vega
     */
    public function lastRevision()
    {
        $this->moveToRevision($this->getLatestRevisionNumber());
        return $this;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return integer
     */
    public function getLatestRevisionNumber()
    {
        if ($this->isBrandNew()) return 0;
        
        $db = $this->getTable()->getAdapter();
        /**
         * @var Zend_Db_Select
         */
        $select = $db->select();
        $select->from(Mira_Core_Constants::TABLE_VEGA)
               ->where("id_vg = ".$this->thisId)
               ->order("rv_vg desc")
               ->limit(1);
        $res = $db->fetchRow($select);
        if (isset($res->rv_vg))
            return $res->rv_vg;
        else
            return 0;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @param integer $revision 
     */
    public function rollbackToRevision ($revision) 
    {
        $latestRv = $this->getLatestRevisionNumber();
        
        // @var Zend_Db_Adapter_Abstract
        $db = $this->getTable()->getAdapter();
        
        // nothing to do there
        if ($revision < 1 || $revision >= $latestRv) {
            throw new Exception("Revision $revision is out of range.");
        } 
        
        // check that the corresponding vegatype has not been deleted
        $latestType = Zend_Registry::get(Mira_Core_Constants::REG_API)->tid($this->id_vgt_vg);
        if (!isset($latestType) || $latestType->status == 'deleted') {
            throw new Exception("Cannot rollback to this revision : restore the vegatype first.");
        }
        
        // 0. save scope id
        $sel = $db->select()->from(Mira_Core_Constants::TABLE_VEGA)
                  ->where("id_vg = " . $this->thisId . " and rv_vg = " . $latestRv);
        $row = $db->fetchRow($sel);
        // @var Mira_Core_Scope
        $latestScope = $this->retrieveScope($row->id_scp_vg);
        
        // 1. delete in between revisions
        
        // a. delete extension table row
        $db->delete($this->_extensionTableName, 
        	"id_vg = " . $this->thisId . 
        	" and rv_vg > " . $revision .
            " and rv_vg <= " . $latestRv);
        
        // b. delete links
        $sql = "DELETE a, b " .
                    "FROM " . Mira_Core_Constants::TABLE_VEGALINK_PROPERTY . " as a " . 
                        "INNER JOIN " . Mira_Core_Constants::TABLE_VEGALINK . " as b ON id_vgl = id_vgl_vlp " . 
                    "WHERE from_id_vg_vgl = " . $this->thisId . 
                    	" and from_rv_vg_vgl > " . $revision .
                        " and from_rv_vg_vgl <= " . $latestRv;
        // @non_sql_agnostic
        $db->query($sql)->execute();
        
        // c. delete base table row
        $db->delete(Mira_Core_Constants::TABLE_VEGA, 
        	"id_vg = " . $this->thisId . 
        	" and rv_vg > " . $revision .
            " and rv_vg <= " . $latestRv);
        
        // 2. mark $revision as enabled
        $db->update(Mira_Core_Constants::TABLE_VEGA,
            array("status_vg" => "enabled"), 
            "id_vg = " . $this->thisId . " and rv_vg = " . $revision);
            
        // 3. move inherited scopes
        // get the scope we are moving to
        $sel = $db->select()->from(Mira_Core_Constants::TABLE_VEGA)
                  ->where("id_vg = " . $this->thisId . " and rv_vg = " . $revision);
        $row = $db->fetchRow($sel);
        // @var Mira_Core_Scope
        $toScope = $this->retrieveScope($row->id_scp_vg);
        // finally move them 
        $toScope->moveDescendants($latestScope->id_scp);
        
        // reinitiliase object
        $this->moveToRevision($revision);
    }
    
    /**
     * @return array array of prop name => value
     */
    public function getVegaProperties($ignoreInternal = false)
    {
        $props = $this->_currentType->getVegaProperties();
        $ret = array();
        foreach ($props as $prop) {
            $propName = $prop->name;
            if (!$ignoreInternal || strpos($propName, "__") !== 0) {
                $ret[$propName] = $this->$propName;
            }
        }
        return $ret;
    }
    
    /**
     * internal
     * 
     * This method should not be called directly. For instance, to change the name
     * do 
     * <code>
     * $vega->name = 'new value';
     * $vega->save();
     * </code>
     * 
     * @access private
     * @param string $localName
     * @param mixed $value
     */
    public function setBaseProperty($localName, $value)
    {
        if ($localName == "rv_vg" && $localName == "id_vg") {
            throw new Mira_Core_Exception_BadRequestException("Cannot change vega id or revision");
            return;
        }
        if (!$this->_currentRow || $this->_currentRow->$localName !== $value)
            $this->_nextRow["base"][$localName] = $value;
    }
    
    /**
     * @access private
     * @param string $localName 
     * @return mixed
     */
    public function getBaseProperty($localName)
    {
        if ($localName == "rv_vg")
            return $this->_currentRevision;
        
        $base = $this->_nextRow["base"];
        if (array_key_exists($localName, $base)) {
            return $base[$localName];
        } else if ($this->isBrandNew()) {
            return null;
        } else {
            return $this->_currentRow->$localName;
        }
    }
    
    /**
     * use $vega->owner
     * 
     * @access private
     * @param integer | Mira_Core_User Owner Value
     */
    public function setOwner($value) 
    {
        if ($value instanceof Mira_Core_User) {
            if (!$this->_currentRow || $this->_currentRow->id_usr_vg !== $value->id_usr)
                $this->_nextRow["base"]["id_usr_vg"] = $value->id_usr;
        } else if ($value || $value === 0) {
            if (!$this->_currentRow || $this->_currentRow->id_usr_vg !== $value)
                $this->_nextRow["base"]["id_usr_vg"] = $value;
        } else {
            throw new Exception("owner property should be of class Mira_Core_User or an Integer.");
        }
    }
    
    /**
     * @access private
     * @return Mira_Core_User
     */
    public function getOwner()
    {
        $base = $this->_nextRow["base"];
        $userId = null;
        if (array_key_exists("id_usr_vg", $base)) {
            $userId = $base["id_usr_vg"];
        }else if($this->id_usr_vg) {
            $userId = $this->id_usr_vg;
        } else if (!$this->isBrandNew()) {
            $userId = $this->_currentRow->id_usr_vg;
        }
        
        if ($userId && $userId != 0)
            return Zend_Registry::get(Mira_Core_Constants::REG_API)->uid($userId);
        else 
            return null;
    }
    
    /**
     * @access private
     */
    private function scopeChanged()
    {
        $this->_nextRow["scopeChanged"] = true;
    }
    
    /**
     * use $vega->scope
     * 
     * @access private
     * @param Mira_Core_Scope $value Scope Value "viewer" or "editer"
     */
    public function setScope($value)
    {
        if ($value instanceof Mira_Core_Scope) {
            $this->_nextRow["scopeChanged"] = true;
            $this->_currentScope = $value;
        }
    }
    
    /**
     * @access private
     * @return Mira_Core_Scope
     */
    public function getScope()
    {
        if (!$this->_currentScope) $this->_currentScope = $this->retrieveScope();
        return $this->_currentScope;
    }
    
    /**
     * use $vega->type
     * 
     * @access private
     * @param integer | Mira_Core_VegaType $value
     */
    public function setType($value = null)
    {
        $typeId = 0;
        if (is_int($value)) {
            $typeId = $value;
        } else if (is_string($value) && intval($value) != 0) {
            $typeId = intval($value);
        } else if ($value instanceof Mira_Core_VegaType) {
            $typeId = $value->id;
        } 
        
        if ($typeId == 0) {
            throw new Exception("value is not an int nor a saved VegaType.");
        }
        
        $this->id_vgt_vg = $typeId;
        $this->moveToRevision(0);
    }
    
    /**
     * 
     * @access private
     * @return Mira_Core_VegaType
     */
    public function getType()
    {
        return $this->_currentType;
    }
    
    /**
     * use $vega->propertyName.
     * If the property contains a space or unauthorized character, use
     * <code>
     * $vega->__set("my property", "value");
     * $value = $vega->__get("my property");
     * </code>
     * 
     * @access private
     * @param Mira_Core_Property |integer the instance of property to modified
     * @param mixed $value the value to the property 
     */
    public function setVegaProperty($vegaProperty, $value)
    {
        $prpId = $vegaProperty instanceof Mira_Core_Property ? $vegaProperty->id : $vegaProperty;
        if (!$vegaProperty->isPrimitive() 
                && $value instanceof Mira_Core_Vega 
                && $vegaProperty->type->id != $value->type->id) {
            throw new Exception("the property is not a " . $vegaProperty->type->name);
        }
        // @todo check if the value has changed
        $this->_nextRow["extension"][$prpId] = $value;
    }

    /**
     * @access private
     * @param Mira_Core_Property $vegaProperty  the instance of property
     * @return mixed
     */
    public function getVegaProperty($vegaProperty) 
    {
        $nr = $this->_nextRow["extension"];
        if (array_key_exists($vegaProperty->id_prp, $nr)) {
            $propertyValue = $nr[$vegaProperty->id_prp];
        	if ($vegaProperty->isPrimitive()) {
                return $propertyValue;
        	} else {
	            if (!($propertyValue instanceof Mira_Core_Vega)) {
	                return Zend_Registry::get(Mira_Core_Constants::REG_API)->vid($propertyValue);
	            } else {
	                return $propertyValue;
	            }
            }
        } else if ($this->isBrandNew()) {
            return null;
        } else {
            if ($vegaProperty->isPrimitive()) {
                $columnName = $vegaProperty->getColumnName();
                return $this->_currentExtensionRow->$columnName;
            } else {
                $table = $this->getTable();
                $db = $table->getAdapter();
                $select = $table->select()
                                ->setIntegrityCheck(false)
                                ->from(Mira_Core_Constants::TABLE_VEGALINK_PROPERTY)
                                ->joinInner(Mira_Core_Constants::TABLE_VEGALINK, 'id_vgl = id_vgl_vlp')
                                ->where('from_id_vg_vgl = ' . $this->thisId)
                                ->where('from_rv_vg_vgl = ' . $this->_currentRevision)
                                ->where('id_prp_vlp = ' . $vegaProperty->id_prp);
                $vegaLink = $db->fetchRow($select);
                if($vegaLink && !empty($vegaLink->to_id_vg_vgl)){
                    $vegaToId = $vegaLink->to_id_vg_vgl;
                    return Zend_Registry::get(Mira_Core_Constants::REG_API)->vid($vegaToId);
                } else {
                    return null;
                }
            }
        }
        return null;        
    }
    
    /**
     * Save and creates a new revision
     * 
     * @return integer vega's id
     */
    public function save()
    {
        // check that some modifications have been performed
        if (!$this->isDirty()) return;
        
        // @var Zend_Db_Table_Abstract
        $table = $this->getTable();
        // @var Zend_Db_Adapter_Abstract
        $db = $table->getAdapter();
        $db->beginTransaction();
        
        try {
            // @var integer
            $nextRevisionNumber = $this->getLatestRevisionNumber() + 1;
            // @var boolean
            $isBrandNew = ($nextRevisionNumber == 1);
            // @var array
            $nr = $this->_nextRow;
            
            // ####################
            // A - PREPARE THE DATA
            // ####################
            
            // 1 - prepare new vega properties
            $newProps_prim = array();
            $newProps_vega = array();
            if (!$isBrandNew) {
                // copy here previous primitive property values
                foreach ($this->_currentExtensionRow as $primKey=>$primValue) {
                    $newProps_prim[$primKey] = $primValue;
                }
                // retrieve and copy here previous vega property values
                $newProps_vega = $this->retrieveAllVegaPropertyLinks($this->id, $this->_currentRevision);
            }
            // override both arrays with new values
            foreach ($nr["extension"] as $vegaPropertyId=>$vegaPropertyValue) {
                $vegaProperty = $this->_id2vegaProperty[$vegaPropertyId];
                if ($vegaProperty->isPrimitive()) {
                    $newProps_prim[$vegaProperty->getColumnName()] = $vegaPropertyValue;
                } else {
                    if ($vegaPropertyValue instanceof Mira_Core_Vega) {                   
                        $newProps_vega[$vegaPropertyId] = $vegaPropertyValue->id; 
                    } else {
                        $newProps_vega[$vegaPropertyId] = $vegaPropertyValue == -1 ? null : $vegaPropertyValue;
                    }
                }
            }
            
            // 2 - prepare the scope
            if ($nr["scopeChanged"] || $isBrandNew) {
                // @var Mira_Core_Scope
                $newScope = null;
                if (isset($this->_currentScope->id_scp)) { 
                    $newScope = $this->_currentScope->duplicate();
                    $this->_currentScope = $newScope;
                } else if ($this->_currentScope) {
                    $newScope = $this->_currentScope;
                } else {
                    $newScope = $this->retrieveScope();
                    $this->_currentScope = $newScope;
                }
                if ($isBrandNew) {
                    // make sure the owner has editor role
                    $owner = $this->getOwner();
                    // there can be only one role by user anyway
                    // $owner can be == 0/null if the vega is a system vega
                    if ($owner)
                        $newScope->addUserRole($owner, Mira_Core_Scope::ROLE_EDITOR); 
                }
                $newScope->save();
            }
            
            // 3 - prepare the base row
            $base = $nr["base"];
            if (!$isBrandNew) {
                $base["id_vg"] = $this->thisId;
                $base["id_usr_vg"] = $this->_currentRow->id_usr_vg;
                if (!array_key_exists("name_vg", $base))
                    $base["name_vg"] = $this->_currentRow->name_vg;
            }
            $base["rv_vg"] = $nextRevisionNumber;
            $base["id_vgt_vg"] = $this->_currentType->id;
            $base["rv_vgt_vg"] = $this->_currentType->revision;
            $base["id_scp_vg"] = $this->_currentScope->id_scp;
            $base["status_vg"] = "enabled";
            
            
            // ########################
            // A - INSERT THE NEW DATA
            // ########################
            
            // 1 - insert base
            $db->insert(Mira_Core_Constants::TABLE_VEGA, $base);
            // retrieve the id
            if ($isBrandNew) {
                $this->thisId = $db->lastInsertId(Mira_Core_Constants::TABLE_VEGA, "id_vg");
            }
            
            // 2 - insert extension row (primitives)
            $newProps_prim["id_vg"] = $this->thisId;
            $newProps_prim["rv_vg"] = $nextRevisionNumber;
            $db->insert($this->_extensionTableName, $newProps_prim);
            
            // 3 - create vega properties links
            foreach ($newProps_vega as $vegaPropertyId=>$linkVegaId) {
                // insert vegalink
                $db->insert(Mira_Core_Constants::TABLE_VEGALINK, 
                    array(	"from_id_vg_vgl" => $this->thisId,
                            "from_rv_vg_vgl" => $nextRevisionNumber,
                            "to_id_vg_vgl" => $linkVegaId,
                            "id_vlt_vgl" => 1 ));
                $linkId = $db->lastInsertId();      
                // insert vegalink_property
                $db->insert(Mira_Core_Constants::TABLE_VEGALINK_PROPERTY,
                    array(	"id_vgl_vlp" => $linkId,
                            "id_prp_vlp" => $vegaPropertyId,
                            "position_vlp" => 1)); // weight_vlp is not used yet, it will be for list of values
            }
        
            // 4 - duplicate generic links (other than vega properties)
            if (!$isBrandNew) {
                $sql = "INSERT INTO vegalink_vgl (from_id_vg_vgl, from_rv_vg_vgl, to_id_vg_vgl, id_vlt_vgl) " . 
                        "SELECT from_id_vg_vgl, $nextRevisionNumber, to_id_vg_vgl, id_vlt_vgl  FROM `vegalink_vgl`  " .
                        "LEFT OUTER JOIN vegalink_property_vlp ON id_vgl_vlp = id_vgl  " .
                        "WHERE from_id_vg_vgl = " . $this->thisId . " AND from_rv_vg_vgl = " . $this->_currentRevision . " AND id_vgl_vlp IS NULL";
                // @non_sql_agnostic
                $db->query($sql);
            } 
            
            // 5 - disable last version
            if (!$isBrandNew) {
                $db->update(Mira_Core_Constants::TABLE_VEGA,
                    array("status_vg" => "disabled"), 
                    "id_vg = " . $this->thisId . " and rv_vg = " . $this->_currentRevision);
            }
        
            $this->moveToRevision($nextRevisionNumber);
            
            $db->commit();
            
            if ($isBrandNew) {
                $this->bus->dispatchEvent(new Mira_Core_Event_VegaEvent(Mira_Core_Event_VegaEvent::CREATE, $this));
            } else {
                $this->bus->dispatchEvent(new Mira_Core_Event_VegaEvent(Mira_Core_Event_VegaEvent::EDIT, $this));
            }
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Mark the latest object's status as "deleted".
     * You can't delete an intermediate vega revision.
     */ 
    public function delete()
    {
        // @var Zend_Db_Adapter_Abstract
        $db = $this->getTable()->getAdapter();
        
        $this->lastRevision();
        
        $db->update(Mira_Core_Constants::TABLE_VEGA,
            array("status_vg" => "deleted"), 
            "id_vg = " . $this->thisId . " and rv_vg = " . $this->_currentRevision);
            
        // we have to unlink vega / vegatypes inheriting from this
        // vega. Those would keep the latest scope of this vega.
        $db->update(Mira_Core_Constants::TABLE_SCOPE, 
            array("inherit_from_scp" => null),
            "inherit_from_scp = " . $this->_currentScope->id_scp);
            
        $this->bus->dispatchEvent(new Mira_Core_Event_VegaEvent(Mira_Core_Event_VegaEvent::DELETE, $this));
    }
    
    /**
     * Restores this vega (set status to "enabled")
     */
    public function restore ()
    {
        // check that the corresponding vegatype has not been deleted
        $latestType = Zend_Registry::get(Mira_Core_Constants::REG_API)->tid($this->id_vgt_vg);
        if (!isset($latestType) || $latestType->status == 'deleted') {
            throw new Exception("Cannot restore this vega : restore its vegatype first.");
        }
        
        $this->lastRevision();
        if ($this->status != "deleted") {
            return;
        }         
        
        // @var Zend_Db_Adapter_Abstract
        $db = $this->getTable()->getAdapter();
        
        $db->update(Mira_Core_Constants::TABLE_VEGA,
            array("status_vg" => "enabled"), 
            "id_vg = " . $this->thisId . " and status_vg = 'deleted'");
            
        $this->bus->dispatchEvent(new Mira_Core_Event_VegaEvent(Mira_Core_Event_VegaEvent::RESTORE, $this));
    }
    
    /**
     * Create a Mira_Core_Reference with all base properties
     * from the vega row
     * 
     * @param Zend_Db_Table_Row $row 
     * @return Mira_Core_Reference
     */
    public static function buildReferenceFromRow ($row)
    {
        // @var Mira_Core_Reference
        $ret = new Mira_Core_Reference();
        $ret->id = $row->id_vg;
        $ret->uid = self::getUID($row->id_vg, $row->rv_vg);
        $ret->name = $row->name_vg;
        $ret->type = "vega";
        $ret->addMeta("ownerId", $row->id_usr_vg);
        $ret->addMeta("creationDate", $row->date_created_vg);
        $ret->addMeta("revision", $row->rv_vg);
        if(isset($row->name_vgt)) $ret->addMeta("typeName", $row->name_vgt);
        $ret->addMeta("typeId", $row->id_vgt_vg);
        return $ret;
    }
    
    /**
     * Delete the whole object including its revisions.
     * 
     * This operation is not reversible!
     */
    public function fullDelete()
    {
    	$db = $this->getTable()->getAdapter();
    	// warnign : about revisions
    	$lastRevision = $this->lastRevision();
    	while($lastRevision->hasPrevious()){
    	    $delVegaScope = "DELETE FROM " . Mira_Core_Constants::TABLE_SCOPE . " WHERE id_scp = " . $lastRevision->scope->id;
    	    $delVegaSCC = "DELETE FROM " .Mira_Core_Constants::TABLE_SCOPE_CUSTOM . " WHERE id_scp_scc = " . $lastRevision->scope->id;
    	    // @non_sql_agnostic
    	    $db->query($delVegaScope);
    	    // @non_sql_agnostic
    	    $db->query($delVegaSCC);
    	    $lastRevision->moveToRevision($lastRevision->revision);  		
    	}
    	$delVegaScope = "DELETE FROM " . Mira_Core_Constants::TABLE_SCOPE . " WHERE id_scp = " . $this->scope->id;
    	$delVegaSCC = "DELETE FROM " .Mira_Core_Constants::TABLE_SCOPE_CUSTOM . " WHERE id_scp_scc = " . $this->scope->id;    		
    	
    	$delVg = "DELETE FROM " . Mira_Core_Constants::TABLE_VEGA . " WHERE id_vg = " . $this->id_vg;
    	$delExtension = "DELETE FROM " . $this->_extensionTableName . " WHERE id_vg = " . $this->id_vg;
    	$delVegaLink = "DELETE FROM " . Mira_Core_Constants::TABLE_VEGALINK . " WHERE to_id_vg_vgl = " . $this->id_vg . " OR from_id_vg_vgl = " . $this->id_vg;
    	
    	// @non_sql_agnostic
    	$db->query($delVg);
    	// @non_sql_agnostic
    	$db->query($delExtension);
    	// @non_sql_agnostic
    	$db->query($delVegaLink);
    	// @non_sql_agnostic
    	$db->query($delVegaScope);
    	// @non_sql_agnostic
    	$db->query($delVegaSCC);
    }
    
    // #################################
    // LINK FUNCTIONS
    // #################################
    
    /**
     * Links two vegas.
     * 
     * @param integer $toVegaId
     * @return integer link's id
     */
    public function addGenericLink($toVegaId)
    {
        $this->getTable()->getAdapter()->insert(Mira_Core_Constants::TABLE_VEGALINK, 
            array(
                "from_id_vg_vgl" => $this->thisId,
                "from_rv_vg_vgl" => $this->revision,
                "to_id_vg_vgl" => $toVegaId,
            	"id_vlt_vgl" => 2
            ));
        return $this->getTable()->getAdapter()->lastInsertId();
    }
    
    /**
     * 
     * @param $toVegaId
     */
    public function deleteGenericLink($toVegaId)
    {
        $this->getTable()->getAdapter()->delete(Mira_Core_Constants::TABLE_VEGALINK, 
        	"to_id_vg_vgl = $toVegaId AND from_id_vg_vgl = $this->id AND from_rv_vg_vgl = $this->revision");
    }
    
    /**
     * Used for identification during serialization
     * 
     * @access private
     * @return string
     */
    public function getSerializableUid()
    {
        return self::getUID($this->thisId, $this->revision);
    }
    
    /**
     * @access private
     * @param integer $id 
     * @param integer $rev
     * @return string
     */
    public static function getUID($id, $rev)
    {
        return "vega|$id";//|$rev";
    }
    
    /**
     * Retrieve Vega Properties that are not primitives
     * 
     * @access private
     * @param integer $id a vega's id
     * @param integer $revision vega's revision
     * @return array propertyId => vegaId
     */
    private function retrieveAllVegaPropertyLinks($id, $revision)
    {
        $links = array();
        
        $db = $this->getTable()->getAdapter();
        
        $select = $this->getTable()->select()
                       ->setIntegrityCheck(false)
                       ->from(Mira_Core_Constants::TABLE_VEGALINK_PROPERTY)
                       ->joinInner(Mira_Core_Constants::TABLE_VEGALINK, Mira_Core_Constants::TABLE_VEGALINK.'.id_vgl = '.Mira_Core_Constants::TABLE_VEGALINK_PROPERTY.'.id_vgl_vlp')
                       ->where("from_id_vg_vgl = $id")
                       ->where("from_rv_vg_vgl = $revision");
                       
        $rs = $db->fetchAll($select);
        
        foreach ($rs as $row) {
            $links[$row->id_prp_vlp] = $row->to_id_vg_vgl;
        }
        
        return $links;
    }
    
    /**
     * @access private
     * @param integer $scopeId 0 to retrieve a new scope
     */
    private function retrieveScope($scopeId = 0)
    {
        $table = Mira_Core_Db_Tables::getInstance()->getScopeTable();
        $ret = null;
        if ($scopeId) {
            $ret = $table->findById($scopeId);
            $ret->scopeOwner = $this;
        } else {
            $ret = $table->createRow();
            $ret->scopeOwner = $this;
        }
        return $ret;
    }
}