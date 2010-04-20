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
 * @access 	   private
 * @category   Mira
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_VegaType_NewRevStore_Properties
{
	// @var array
	public $added;
	// @var array
	public $edited;
	// @var array
	public $removed;

	public function __construct()
	{
		$this->added = array();
		$this->edited = array();
		$this->removed = array();
	}
}

/**
 * @access 	   private
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Command
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_VegaType_NewRevStore
{
	// @var array
	public $base;
	// @var Mira_Core_VegaType_NewRevStore_Properties
	public $properties;

	public function __construct()
	{
		$this->base = array();
		$this->properties = new Mira_Core_VegaType_NewRevStore_Properties();
	}

	// @return boolean
	public function isDirty()
	{
		return count($this->base)
		+ count($this->properties->added)
		+ count($this->properties->removed)
		+ count($this->properties->edited) > 0;
	}
}

/**
 * Describes a Vega - Equivalent to a "Class" in OOP or a "Class" in RDF
 * 
 * See README.md for more info.
 * 
 * @category   Mira
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_VegaType extends Mira_Utils_Pretty_Row implements Mira_Utils_IVersionable
{
    /**
     * this is used by AMF to specify remote AS classname
     * @access private
     * @var string
     */
	public $_explicitType = "com.vega.core.api.vega.VegaType";
	/**
     * @access private
     */
	public $_serializableUid = null;

	// @var integer main id
	public $thisId;

	// @var Mira
	private $api;
	// @var Mira_Utils_Event_CommandBus
	private $bus;
	
	// objects containing current revision
	// @var integer
	private $_currentRevision;
	// @var Zend_Db_Table_Row
	private $_currentRevRow;
	// @var array
	private $_currentVegaProperties;

	// objects containing next revision (current modifs bufferized)
	// @var Mira_Core_VegaType_NewRevStore
	private $_nextRevStore;
	
    /**
     * This should not be instantiated directly. Use {@link Mira::selectVegaTypes()} or {@link Mira::createVegaType()}
     * A Mira_Core_Vega is the root object of all Mira object database. It supports versionning and security.
     * Read README.md for more information.
     * 
     * It exposes those properties (you won't have them by autocomplete / intellisense / phpdoc
     * as they are accessed by magic php functions) :
     *   - id
     *   - fqn : fully qualified name - should be unique as opposed to name which is not
     *   - revision 0 = new / unsaved, 1 = first revision
     *   - name
     *   - creationDate
     *   - status : enabled, disabled (not the latest version) or deleted (in trash)
     *   - owner : Mira_Core_User
     *   - vegaProperties : associative array prop name => value
     * 
     * @param $config
     */
	public function __construct($config)
	{
		// this table exposed properties
		$properties = array();
		$properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "id", false, true, "baseProperty", "id_vgt");
		$properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "revision", false, true, "baseProperty", "rv_vgt");
		$properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "status", false, true, "baseProperty", "status_vgt");
		$properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "name", false, true, "baseProperty", "name_vgt");
		$properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "fqn", false, true, "baseProperty", "fqn_vgt");
		$properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "creationDate", false, true, "baseProperty", "date_created_vgt");
		$properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "owner", false, true, "owner");
		$properties[] = new Mira_Utils_Pretty_Property_Delegate($this, "vegaProperties", false, true, "vegaProperties");

		// API
		$this->api = Zend_Registry::get(Mira_Core_Constants::REG_API); 
		// BUS
		$this->bus = Zend_Registry::get(Mira_Core_Constants::REG_BUS); 
		
		parent::__construct($config, $properties);
	}

	/**
	 * init() function is called after a Table->createNew() or a Table->fetchRow() 
	 * is called
	 * @access private
	 */
	public function init ()
	{
		parent::init();
		$rev = $this->rv_vgt;
		if ($this->id_vgt == null) {
			$rev = 0;
		} else {
			$this->thisId = $this->id_vgt;
			$this->_serializableUid = self::getUID($this->thisId, $rev);
		}
		$this->moveToRevision($rev);
	}

	/**
     * Does this vegatype need to be saved
	 * 
     * @return boolean
     */
	public function isDirty()
	{
	    // analyze properties to detect if there has been some modifications
	    if (!$this->isBrandNew()) {
    	    foreach ($this->_currentVegaProperties as $propId => $prop) {
                if ($prop->isDirty()) {
                    $this->vegaPropertyEdited($prop);
                }
    	    }
	    }
		return (isset($this->_nextRevStore) ? $this->_nextRevStore->isDirty() : false);
	}

	/**
	 * {@inheritdoc}
     * 
	 * @param integer $revision the VegaType's revision
     */
	public function moveToRevision($revision)
	{
		// next revision store
		$this->_nextRevStore = new Mira_Core_VegaType_NewRevStore();

		// current revision
		$this->_currentRevision = $revision;
		$this->_serializableUid = self::getUID($this->thisId, $revision);

		if (!$this->isBrandNew()) {
			// 1. populate current row
			$db = $this->getTable()->getAdapter();
			$select = $db->select();
			$select->from(Mira_Core_Constants::TABLE_VEGATYPE)
    			   ->where("id_vgt = ".$this->thisId)
    			   ->where("rv_vgt = ". $revision);
			$this->_currentRevRow = $db->fetchRow($select);
			// 2. get current properties
			$props = $this->retrieveProperties($this->thisId, $revision);
			$this->_currentVegaProperties = array();
			foreach ($props as $prop) {
				$this->_currentVegaProperties[$prop->id_prp] = $prop;
			}
		}
	}

	/**
	 * {@inheritdoc}
     * 
	 * @param integer $revision
     */
	public function rollbackToRevision($revision)
	{
		$latestRv = $this->getLatestRevisionNumber();

		// @var Zend_Db_Adapter_Abstract
		$db = $this->getTable()->getAdapter();

		// nothing to do there
		if ($revision < 1 || $revision >= $latestRv) {
			throw new Exception("Revision $revision is out of range.");
		}

		// change extension table structure
		// perform a diff between vegatypes to see added columns since $revision
		$latestType = $this->api->tidAndRevision($this->thisId, $latestRv);
		$latestTypeProps = $latestType->getVegaProperties();
		$latestTypePropsIds = array();
		foreach ($latestTypeProps as $prop) {
			if ($prop->isPrimitive()) {
				$latestTypePropsIds[] = $prop->id_prp;
			}
		}
		$toReviType = $this->api->tidAndRevision($this->thisId, $revision);
		$toReviTypeProps = $toReviType->getVegaProperties();
		$sql = "ALTER TABLE " . $this->getTableName() . " ";
		$delCols = array();
		foreach ($toReviTypeProps as $prop) {
			if ($prop->isPrimitive()
			&& !in_array($prop->id_prp, $latestTypePropsIds)) {
				$delCols[] = "DROP COLUMN `" . $prop->getColumnName() . "`";
			}
		}
		if (count($delCols) > 0) {
			$sql = $sql . implode(", ", $delCols);
			// @non_sql_agnostic
			$db->query($sql);
		}

		// delete concerning vegatype rows
		$db->delete(Mira_Core_Constants::TABLE_VEGATYPE,
        	"id_vgt = " . $this->thisId . 
        	" and rv_vgt > " . $revision .
            " and rv_vgt <= " . $latestRv);

		// delete concerning vegaproperty rows
		$db->delete(Mira_Core_Constants::TABLE_VEGAPROPERTY,
        	"id_vgt_prp = " . $this->thisId . 
        	" and rv_vgt_prp > " . $revision .
            " and rv_vgt_prp <= " . $latestRv);

		// mark revision as enabled
		$db->update(Mira_Core_Constants::TABLE_VEGATYPE,
		array("status_vgt" => "enabled"),
            "id_vgt = " . $this->thisId . " and rv_vgt = " . $revision);

		$this->moveToRevision($revision);
		
        $this->bus->dispatchEvent(new Mira_Core_Event_VegaTypeEvent(Mira_Core_Event_VegaTypeEvent::ROLLBACK, $this));
	}

	/**
	 * Create a new vega of this type
	 * 
	 * @param integer|Mira_Core_User $owner id or VO of the user
	 * @param string $name of the vega to create
     * @return boolean
     */
	public function createVega($name = null, $owner = null)
	{
		return $this->api->createVega($this, $name, $owner);
	}

	/**
	 * @return integer
	 */
	public function save()
	{
		// no modifs performed => no save needed
		if (!$this->isDirty()) return;
		
		// check that property names are unique
		$temp = array();
		foreach ($this->getVegaProperties() as $vp) {
		    $name = strtolower($vp->name);
		    if (!$name || Mira_Utils_String::isEmpty($name)) {
		        throw new Mira_Core_Exception_BadRequestException("Cannot have a property without a name");
		    }
		    if (!in_array($name, $temp)) {
		        $temp[] = $name;
		    } else {
		        throw new Mira_Core_Exception_BadRequestException("Cannot have 2 properties of the same name '$name' (Case insensitive)");
		    }
		}

		// @var Zend_Db_Table_Abstract
		$table = $this->getTable();
		// @var Zend_Db_Adapter_Abstract
		$db = $table->getAdapter();
		// @var integer
		$nextRevisionNumber = $this->getLatestRevisionNumber() + 1;
		// @var boolean
		$isNewType = ($nextRevisionNumber == 1);

		// A - commit base
		// @var array
		$base = $this->_nextRevStore->base;
		if (!$isNewType) {
			$base["id_vgt"] = $this->thisId;
			$base["id_usr_vgt"] = $this->_currentRevRow->id_usr_vgt;
			$base["date_created_vgt"] = $this->_currentRevRow->date_created_vgt;
			if (!array_key_exists("name_vgt", $base)) {
				$base["name_vgt"] = $this->_currentRevRow->name_vgt;
			}
		    if (!array_key_exists("fqn_vgt", $base)) {
				$base["fqn_vgt"] = $this->_currentRevRow->fqn_vgt;
			}
		}
		// we do not allow null FQNs
		if (!array_key_exists("fqn_vgt", $base) || !$base["fqn_vgt"]) {
		    $base["fqn_vgt"] = $this->buildFQN();
		}
		$base["rv_vgt"] = $nextRevisionNumber;
		$base["status_vgt"] = "enabled";
		if (isset($base["id_usr_vgt"]))
		    $id_usr = $base["id_usr_vgt"];

        $db->insert(Mira_Core_Constants::TABLE_VEGATYPE, $base);


		if ($isNewType) {
			$this->thisId = $db->lastInsertId(Mira_Core_Constants::TABLE_VEGATYPE, "id_vgt");
			$this->_serializableUid = self::getUID($this->thisId, $nextRevisionNumber);
		}

		// B - creating / updating the extension table
		// @var string
		$sql = null;
		$executeSql = false;
		// @var Mira_Core_VegaType_NewRevStore_Properties
		if ($isNewType) {
			// @todo check the primitive type
			$sql = "CREATE TABLE ".$this->getTableName()." ( ".
                        "id_vg int, " . 
            			"rv_vg int";
			$executeSql = true;
			// creating new columns
			foreach ($this->_nextRevStore->properties->added as $property) {
				$property->id_vgt_prp = $this->thisId;
				$property->rv_vgt_prp = $nextRevisionNumber;
				$property->save();
				$propertyId = $property->id;
				if ($property->isPrimitive()) {
					$sql .= ", `" . $propertyId . "` " . $property->getSQLType();
				}
			}
			$sql .= ") ENGINE=InnoDB";
		} else {

			// disable last revision
			$db->update(Mira_Core_Constants::TABLE_VEGATYPE,
			array("status_vgt" => "disabled"),
                "id_vgt = " . $this->thisId . " and rv_vgt <= " . $this->_currentRevision);

			$sql = "ALTER TABLE ".$this->getTableName();
			// creating new columns
			$first = true;
			foreach ($this->_nextRevStore->properties->added as $property) {
				$property->id_vgt_prp = $this->thisId;
				$property->rv_vgt_prp = $nextRevisionNumber;
				$property->save();
				$propertyId = $property->id;
				if ($property->isPrimitive()) {
					$executeSql = true;
					if ($first) {
						$sql .= " ADD `" . $propertyId . "` " . $property->getSQLType();
						$first = false;
					} else {
						$sql .= ", ADD `" . $propertyId . "` " . $property->getSQLType();
					}
				}
			}
			// save other columns
			foreach ($this->_nextRevStore->properties->edited as $property) {
				$property = $property->duplicate();
				$property->id_vgt_prp = $this->thisId;
				$property->rv_vgt_prp = $nextRevisionNumber;
				$property->save();
			}
			// save other columns
			foreach ($this->_currentVegaProperties as $property) {
				if (       !isset($this->_nextRevStore->properties->removed[$property->id_prp])
				&& !isset($this->_nextRevStore->properties->edited[$property->id_prp])) {
					$property = $property->duplicate();
					$property->id_vgt_prp = $this->thisId;
					$property->rv_vgt_prp = $nextRevisionNumber;
					$property->save();
				}
			}
		}

		// go create / change extension table
		if ($executeSql) {
		    // @non_sql_agnostic
			$db->query($sql);
		}

		// reinit all
		$this->moveToRevision($nextRevisionNumber);
            
        if ($isNewType) {
            $this->bus->dispatchEvent(new Mira_Core_Event_VegaTypeEvent(Mira_Core_Event_VegaTypeEvent::CREATE, $this));
        } else {
            $this->bus->dispatchEvent(new Mira_Core_Event_VegaTypeEvent(Mira_Core_Event_VegaTypeEvent::EDIT, $this));
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

		// delete all concerning vegas
		$db->update(Mira_Core_Constants::TABLE_VEGA,
		array("status_vg" => "deleted"),
            "id_vgt_vg = " . $this->thisId . " " . 
            "and status_vg = 'enabled'");

		// delete the vegatype
		$db->update(Mira_Core_Constants::TABLE_VEGATYPE,
		array("status_vgt" => "deleted"),
            "id_vgt = " . $this->thisId . " and rv_vgt = " . $this->_currentRevision);
		
        $this->bus->dispatchEvent(new Mira_Core_Event_VegaTypeEvent(Mira_Core_Event_VegaTypeEvent::DELETE, $this));
	}

	/**
	 * Create the value of fqn
	 * if the it is not defined
	 * 
	 * @access private
	 * @return string
	 */
	public function buildFQN()
	{
	    $owner = $this->getOwner();
		if ($owner) {
			return "User_" . $owner->id . "_" . ereg_replace(" ","",$this->_nextRevStore->base["name_vgt"]); 
		} else {
			return "Mira_Core_" . ereg_replace(" ","",$this->_nextRevStore->base["name_vgt"]);
		}
	}
	/**
     * Restores this vegatype (set status to "enabled")
	 */
	public function restore()
	{
		// @var Zend_Db_Adapter_Abstract
		$db = $this->getTable()->getAdapter();

		$this->lastRevision();
		if ($this->status != "deleted") {
			return;
		}
		
		// Verify the name and change it if is necesary
		// @todiscuss ===
		$name = $this->name;
		$fqn = $this->fqn;
		if(($newname = $this->checkName()) !== true) {
		    $name = $newname;
		    $this->_nextRevStore->base["name_vgt"] = $name;
		}
		
	    $db->update(Mira_Core_Constants::TABLE_VEGATYPE,
		    array("status_vgt" => "enabled", "name_vgt" => $name, "fqn_vgt" => $fqn),
            	"id_vgt = " . $this->thisId . " and status_vgt = 'deleted'");
		$this->lastRevision();
		
        $this->bus->dispatchEvent(new Mira_Core_Event_VegaTypeEvent(Mira_Core_Event_VegaTypeEvent::RESTORE, $this));
	}
	
    /**
     * returns true if the name is not conflictual with another type
     * returns a proposed new name if there is a conflict
     * 
     * @access private
	 * @return mixed
	 */
	public function checkName()
	{
		$name = $this->name;
		$i = 1;
		$vegaTypes = $this->api->tname($name);
		while ($vegaTypes){
			$name = $name . " " . $i;
			$vegaTypes = $this->api->tname($name);
			$i++;
		}
		if($name == $this->name)
		    return true;
		else
		    return $name;
	}

	/**
	 * Check if the vegaType is new
	 * 
	 * @return boolean
	 */
	public function isBrandNew()
	{
		return $this->_currentRevision == 0;
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
	 * @return Mira_Core_VegaType
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
	 * @return Mira_Core_VegaType
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
	 * @return Mira_Core_VegaType
	 */
	public function firstRevision()
	{
		$this->moveToRevision(1);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @return Mira_Core_VegaType
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
	public function getRevisionNumber()
	{
		return $this->_currentRevision;
	}

	/**
	 * {@inheritdoc}
	 * 
     * @return integer
     */
	public function getLatestRevisionNumber()
	{
		if ($this->_currentRevision == 0) return 0;

		$db = $this->getTable()->getAdapter();
		// @var Zend_Db_Select
		$select = $db->select();
		$select->from(Mira_Core_Constants::TABLE_VEGATYPE)
		->where("id_vgt = ".$this->id)
		->order("rv_vgt desc")
		->limit(1);
		$res = $db->fetchRow($select);
		if (isset($res->rv_vgt))
		return $res->rv_vgt;
		else
		return 0;
	}

	/**
	 * Used to update name or such values of the principal vegatype_vgt table
	 * 
	 * @access private
	 * @param string $localName Name of Base Property
	 * @param mixed $value The Value of the Base Property
     */
	public function setBaseProperty($localName, $value)
	{
		if ($localName == "rv_vgt" && $localName == "id_vgt") {
            throw new Mira_Core_Exception_BadRequestException("Cannot change type id or revision");
		}
		$this->_nextRevStore->base[$localName] = $value;
	}

	/**
	 * @access private
	 * @param string $localName Name of the Base Property
     * @return mixed
     */
	public function getBaseProperty($localName)
	{
		if ($localName == "rv_vg")
		return $this->_currentRevision;

		$base = $this->_nextRevStore->base;
		if (array_key_exists($localName, $base)) {
			return $base[$localName];
		} else if ($this->isBrandNew()) {
			throw new Zend_Exception("VegaType isn't save yet");
		} else {
			return $this->_currentRevRow->$localName;
		}
	}

   /** 
	* use $vegatype->owner
	*  
	* @access private
    * @param Mira_Core_User | integer $value Owner to put 
    * @return boolean
    */
	public function setOwner($value)
	{
	    if (!$value) return;
		if ($value instanceof Mira_Core_User) {
			$this->_nextRevStore->base["id_usr_vgt"] = $value->id_usr;
		} else if (is_int($value)) {
			$this->_nextRevStore->base["id_usr_vgt"] = intval($value);
		} else {
			throw new Exception("owner property should be of class Mira_Core_User.");
		}
	}

	/**
	 * @access private
     * @return mixed
     */
	public function getOwner()
	{
		$base = $this->_nextRevStore->base;
		if (array_key_exists("id_usr_vgt", $base)) {
			return $this->api->uid($base["id_usr_vgt"]);
		} else if ($this->isBrandNew()) {
			return null;
		} else {
			return $this->api->uid($this->_currentRevRow->id_usr_vgt);
		}
	}
	 
	/**
	 * this will do stupid stuff
	 * no incremental revisionning. just removeAll and addAll
	 * 
	 * @access private
	 * @param array $value properties to add
	 */
	public function setVegaProperties($value)
	{
		// remove all properties
		if (!$this->isBrandNew()) {
			foreach ($this->_currentVegaProperties as $property) {
				$this->removeProperty($property->id_prp);
			}
		}

		// add all properties
		foreach ($value as $property) {
			$this->addProperty($property);
		}
	}

	/**
	 * this returns a view of this type vega properties, including modified
	 * properties. So /!\ warning, some properties might not have been
	 * saved ($property->id undefined)
	 * 
	 * @access private
	 * @return array
	 */
	public function getVegaProperties()
	{
		$ret = array();
		if (!$this->isBrandNew()) {
			foreach ($this->_currentVegaProperties as $property) {
				if (       !isset($this->_nextRevStore->properties->removed[$property->id_prp])
				&& !isset($this->_nextRevStore->properties->edited[$property->id_prp])) {
					$ret[] = $property;
				}
			}
		}
		foreach ($this->_nextRevStore->properties->added as $property) {
			$ret[] = $property;
		}
		foreach ($this->_nextRevStore->properties->edited as $property) {
			$ret[] = $property;
		}

		return $ret;
	}

	/**
	 * Remove a property
	 * 
	 * @param Mira_Core_Property the property to remove						
     */
	public function removeProperty($property)
	{
		// look if this property is in the the next revision store for addtion
		// if so, remove it.
		$addedProperties = $this->_nextRevStore->properties->added;
		foreach ($addedProperties as $key=>$addedProperty) {
			if ($property->name == $addedProperty->name) {
				unset($addedProperties[$key]);
				return;
			}
		}

		// the property does not exist in the current rev, for sure
		if (!isset($property->id_prp)) return;

		// this property is in the current revision properties.
		if (!$this->isBrandNew()
		    && isset($this->_currentVegaProperties[$property->id_prp])) {
			$this->_nextRevStore->properties->removed[$property->id_prp] = $property;
		}
	}

	/**
	 * Remove a property by its id
	 * 
	 * @param integer $propertyId 
      */
	public function removePropertyWithId($propertyId)
	{
		$prop = Mira_Core_Db_Tables::getInstance()->getVegaPropertyTable()->findById($propertyId);
		if ($prop) {
			$this->removeProperty($prop);
		} else {
			throw new Error("The property $propertyId was not found");
		}
	}

	/**
	 * Add a property to this VegaType
	 * 
	 * @param Mira_Core_Property $property instance of property 
     */
	protected function addProperty($property)
	{
		// check that the property is not already in the type
		if (isset($property->id_prp)
		&& isset($this->_currentVegaProperties[$property->id_prp]))
		return;

		// then add it to the next row
		// this will replace the existing added property if necessary
		$this->_nextRevStore->properties->added[] = $property;
	}

	/**
	 * Create a new property
	 * 
	 * @param string $name value of name the property to create
	 * @param mixed $type value of type the property to create
     * @return Mira_Core_Property
     */
	public function createProperty($name = null, $type = null)
	{
		$prop = Mira_Core_Db_Tables::getInstance()->getVegaPropertyTable()->createRow();
		$prop->propertyOwner = $this;
		if (isset($name)) $prop->name = $name;
		if (isset($type)) $prop->type = $type;
		$this->addProperty($prop);
		return $prop;
	}

	/**
	 * Mark this property as "to be saved"
	 * 
	 * @param Mira_Core_Property $property 
     */
	private function vegaPropertyEdited($property)
	{
		if (!$this->isBrandNew()) {
			// check that the property is already in the type
			if (   !isset($property->id_prp)
			|| !isset($this->_currentVegaProperties[$property->id_prp]))
			return;

			$this->_nextRevStore->properties->edited[$property->id_prp] = $property;
		}
	}


	/**
	 * The real table name used in SQL behind
	 * 
	 * @access private
	 * @return string
	 */
	public function getTableName ()
	{
		if (!isset($this->thisId)) {
			throw new Exception("VegaType has not been saved yet.");
		}
		return "vega_" . $this->thisId;
	}

	/**
	 * @param string $name  
	 * @return Mira_Core_Property
	 */
	public function propertyWithName ($name)
	{
		foreach ($this->_currentVegaProperties as $id=>$prop) {
			if ($prop->name == $name) return $prop;
		}
		return null;
	}

	/**
	 * @param integer $id 
	 * @return Mira_Core_Property
	 */
	public function propertyWithId ($id)
	{
		return $this->_currentVegaProperties[$id];
	}

	/**
	 * Get UID
	 * 
	 * @access private
	 * @param integer $id
	 * @param integer $rev
	 * @return string
	 */
	public static function getUID($id, $rev)
	{
		return "vegaType|$id";//|$rev";
	}
	
    /**
     * Delete the whole object including its revisions and associated Vegas.
     * 
     * This operation is not reversible!
     */
	public function fullDelete()
	{
	    // select all vegas of this type, even those in trash
		$vegas = $this->api->selectVegas()->where("vegaType", $this)->where("status", "both")->fetchAll(); 
		foreach ($vegas as $vega) {
			$vega->fullDelete();
		}
		// prepare the request
		$db = $this->getTable()->getAdapter();
		$delVgt = "DELETE FROM " . Mira_Core_Constants::TABLE_VEGATYPE . " WHERE id_vgt = " . $this->id_vgt;
		$delPrp = "DELETE FROM " . Mira_Core_Constants::TABLE_VEGAPROPERTY . " WHERE id_vgt_prp = " . $this->id_vgt . " OR type_id_vgt_prp = " . $this->id_vgt;
		$delTable = "DROP TABLE " . $this->getTableName();
		// @non_sql_agnostic
		$db->query($delVgt);
		// @non_sql_agnostic
		$db->query($delPrp);
		// @non_sql_agnostic
		$db->query($delTable);
	}
	
    /**
     * @access private
     */
	private function retrieveProperties($id, $revision)
	{
	    $table = Mira_Core_Db_Tables::getInstance()->getVegaPropertyTable();
	    
        $sel =  $table->select(true)
                      ->setIntegrityCheck(false)
                      ->joinInner(Mira_Core_Constants::TABLE_VEGATYPE, 'id_vgt_prp = id_vgt AND rv_vgt_prp = rv_vgt')
                      ->where("id_vgt = $id")
                      ->order(array('position_prp ASC'));
              
        if ($revision > 0) {
            // get the latest revision
            $sel->where("rv_vgt = $revision");
        } else {
            return array();
        }

        $rs = $table->fetchAll($sel);
        $ret = array();
        foreach ($rs as $row) {
            $row->propertyOwner = $this;
            $ret[] = $row;
        }
        
        return $ret;
	}
}