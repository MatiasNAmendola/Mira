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
 * @subpackage Select
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * See README.md or http://github.com/getvega/mira for selects usage.
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Select
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Select_VegaSelect extends Mira_Core_Select_Abstract
{
    const SELECTOR_TYPE = "vegaType";
    
    const LINK_TYPE_ANY = "any";
    const LINK_TYPE_VEGAPROPERTY = "vegaproperty";
    const LINK_TYPE_GENERIC = "generic";
    
    const LINK_DIRECTION_ANY = "any";
    const LINK_DIRECTION_FROM = "from";
    const LINK_DIRECTION_TO = "to";
    
    /**
     * Vega Table (vega_vg)
     * @var Zend_Db_Table_Abstract
     */
    protected $table;
    
    protected $store_idWhere;
    protected $store_revisionWhere;
    protected $store_nameWhere;    
    protected $store_statusWhere;
    protected $store_securityWhere;
    protected $store_typeWhere;
    // "property wheres" have special attention
    // they require multiple joins, and we should 
    // this, not executing it when not necessary
    protected $store_propertyWheres;
    protected $store_linkedTos;
    protected $store_options = array();
    // need to retrieve some details from these properties
    protected $prepare_properties;
    protected $prepare_details;
    
    // ###################################################
    // API
    // ###################################################
    
    /**
     * {@inheritdoc}
     * Mira_Core_Select_VegaSelect special selectors:
     * <ol>
     * <li>
     * 	<ul>
     * 		<li>$selector: {@link SELECTOR_TYPE}</li>
     * 		<li>$value: Mira_Core_VegaType | vegatype id | vegatype name | Mira_Core_Select_VegaTypeSelect</li>
     * 		<li>$config: "strict" | "permissive" (only used when the value is a name)</li>
     * 	</ul>
     * </li>
     * </ol>
     * 
     * @param string|id $selector
     * @param mixed $value
     * @param mixed $config
     * @return Mira_Core_Select_VegaSelect
     */
    public function where($selector, $value = null, $config = null) 
    {
        $args = array($selector, $value, $config);
        switch ($selector) {
            case self::SELECTOR_ID:
                $this->store_idWhere = $args;
                break;
            case self::SELECTOR_REVISION:
                $this->store_revisionWhere = $args;
                break;
            case self::SELECTOR_NAME:
                $this->store_nameWhere = $args;
                break;
            case self::SELECTOR_STATUS:
                $this->store_statusWhere = $args;
                break;
            case self::SELECTOR_SECURITY:
                $userId = $value instanceof Mira_Core_User ? $value->id : $value;
                if ($userId && (!$this->api->getUser() || $userId != $this->api->getUser()->id)
                            && ($this->api->getAuthLevel() < Mira::AUTHLEVEL_APPLICATION)) {
                    throw new Exception("Your API key is not suitable for this operation (trying to retrieve other user's data). Ask for a 'system' API Key to your Vega administrator.");
                } else {
                    $this->store_securityWhere = $args;
                }
                break;
            case self::SELECTOR_TYPE:
                $this->store_typeWhere = $args;
                break;
            default: // it is a vega property
                $this->prepare_properties[] = array("from", $selector, $value, $config);
                $this->store_propertyWheres[] = $args;
                break;
        }
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function limit($count = 50, $offset = 0)
    {
        $this->store_options["offset"] = $offset;
        $this->store_options["count"] = $count;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function order($byProperty, $ascending = true) 
    {
        $this->store_options["orderBy"] = $byProperty;
        $this->store_options["orderAsc"] = $ascending;
        return $this;
    }
    
    /**
     * @param $value Mira_Core_Vega | vega id | vega name | Mira_Core_Select_VegaSelect
     * @param $linkType {@link LINK_TYPE_ANY} | {@link LINK_TYPE_VEGAPROPERTY} | {@link LINK_TYPE_GENERIC}
     * @param $linkDirection {@link LINK_DIRECTION_FROM} | {@link LINK_DIRECTION_TO} | {@link LINK_DIRECTION_BOTH}
     * @return Mira_Core_Select_VegaSelect
     */
    public function linkedTo($value, $linkType = self::LINK_TYPE_ANY, $linkDirection = self::LINK_DIRECTION_ANY, $config = "strict")
    {
        if ($linkType != self::LINK_TYPE_ANY || $linkType != self::LINK_TYPE_GENERIC) {
            if ($linkDirection != self::LINK_DIRECTION_TO)
                $this->prepare_properties[] = array("from", $linkType, $value, $config);
            if ($linkDirection != self::LINK_DIRECTION_FROM)
                $this->prepare_properties[] = array("to", $linkType, $value, $config);
        }
        $this->store_linkedTos[] = array($value, $linkType, $linkDirection, $config);
        return $this;
    }
    
    // ###################################################
    // INTERNALS
    // ###################################################
    
    /**
     * {@inheritdoc}
     * @return Zend_Db_Table_Select
     */
    protected function prepareSelect($alias = null)
    {
        // @var Zend_Db_Table_Select
        $select = parent::prepareSelect($alias ? $alias : Mira_Core_Constants::TABLE_VEGA);

        // all renders
        $select = $this->renderIdWhere($this->store_idWhere, $select);
        $select = $this->renderRevisionWhere($this->store_revisionWhere, $select);
        $select = $this->renderNameWhere($this->store_nameWhere, $select);
        $select = $this->renderSecurityWhere($this->store_securityWhere, $select);
        $select = $this->renderStatusWhere($this->store_statusWhere, $select);
        $select = $this->renderTypeWhere($this->store_typeWhere, $select);
        // prepare the properties: find the corresponding vegatypes
        // and populate $this->prepare_details
        if (count($this->prepare_properties) > 0)
            $this->prepareProperties();
        
        // finally, add the complex selects
        $select = $this->renderPropertyWheres($this->store_propertyWheres, $select);
        if ($select) $select = $this->renderLinks($this->store_linkedTos, $select);
        if ($select) $select = $this->renderOptions($this->store_options, $select);
        return $select;
    }
    
    /**
     * This does a first db query to retrieve some more detailed info 
     * about the filters (which type is that property, what is its id, ...)
     */
    protected function prepareProperties()
    {
        $selects = array();
        
        foreach ($this->prepare_properties as $a) {
            
            list($direction, $propertyNameOrId, $relatedContent, $relatedContentConfig) = $a;
            
            $select = $this->table->getAdapter()->select()
                         ->from(Mira_Core_Constants::TABLE_VEGAPROPERTY)
                         ->where(Mira_Utils_String::isId($propertyNameOrId) ? "id_prp = $propertyNameOrId" : "name_prp = '$propertyNameOrId'")
                         ->group("id_prp");
            
            $fromSet = false;
            
            if ($this->store_typeWhere) {
                if ($direction == "from") $fromSet = true;
                $this->renderTypeWhere(null, $select, 
                            $direction == "from" ? "id_vgt_prp" : "type_id_vgt_prp",
                            $direction == "from" ? "rv_vgt_prp" : null,
                            $direction == "from" ? "vegatype_vgt" : "to_vegatype_vgt");
            }
            if ($relatedContent instanceof Mira_Core_Select_VegaSelect) {
                if ($direction == "to") $fromSet = true;
                $relatedContent->renderTypeWhere(null, $select, 
                            $direction == "from" ? "type_id_vgt_prp" : "id_vgt_prp",
                            $direction == "from" ? null : "rv_vgt_prp",
                            $direction == "from" ? "to_vegatype_vgt" : "vegatype_vgt");
            }
            // @todo we should group by to vegatype no ? or limit to enabled [to as direction] vegatypes ?
            if (!$fromSet) {
                $select->joinInner(Mira_Core_Constants::TABLE_VEGATYPE, "id_vgt = id_vgt_prp");
            }
            
            $selects[] = $select;
        } 
        
        $select = $this->table->getAdapter()->select()->union($selects);
        $rs = $select->query(Zend_Db::FETCH_ASSOC)->fetchAll();
                   
        
        $detailsById = array();
        $detailsByName = array();
        foreach ($rs as $row) {
            $details = array(
                $row["id_prp"], 
                $row["name_prp"], 
                $row["id_vgt"], 
                $row["rv_vgt"], 
                $row["type_id_vgt_prp"] ? false : true, 
                $row["type_id_vgt_prp"] ? $row["type_id_vgt_prp"] : $row["type_id_prm_prp"],
                $row["fqn_vgt"]
                );
            $detailsById[$row["id_prp"]] = $details; 
            $detailsByName[$row["name_prp"]] = $details; 
        }
        
        $details = array();
        foreach ($this->prepare_properties as $a) {
            list($direction, $propertyNameOrId, $relatedContent, $relatedContentConfig) = $a;
            $curDetails = null;
            if (Mira_Utils_String::isId($propertyNameOrId)) {
                if (isset($detailsById[$propertyNameOrId]))   
                    $curDetails = $detailsById[$propertyNameOrId];
            } else {
                if (isset($detailsByName[$propertyNameOrId])) 
                    $curDetails = $detailsByName[$propertyNameOrId];
            }
            $details[$propertyNameOrId] = $curDetails;
        } 
        $this->prepare_details = $details;
    }
    
    /**
     * {@inheritdoc}
     * @param Zend_Db_Table_Row $row
     * @return Mira_Core_Reference
     */
    protected function buildReferenceFromRow($row)
    {
        // @var Mira_Core_Reference
        $ret = new Mira_Core_Reference();
        $ret->id = $row["id_vg"];
        $ret->uid = Mira_Core_Vega::getUID($row["id_vg"], $row["rv_vg"]);
        $ret->name = $row["name_vg"];
        $ret->type = "vega";
        $ret->addMeta("ownerId", $row["id_usr_vg"]);
        $ret->addMeta("creationDate", $row["date_created_vg"]);
        $ret->addMeta("revision", $row["rv_vg"]);
        $ret->addMeta("typeName", $row["name_vgt"]);
        $ret->addMeta("typeId", $row["id_vgt"]);
        $ret->addMeta("fqn", $row["fqn_vgt"]);
        return $ret;
    }
    
    // ###################################################
    // RENDERERS
    // ###################################################
    
    /**
     * @param array $args
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderIdWhere($args, $select)
    {
        if (!$args) return $select;
        list ($selector, $value, $config) = $args;
        $select->where($this->currentAlias . ".id_vg = $value");    
        return $select;        
    }
    
    /**
     * @param array $args
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderRevisionWhere($args, $select)
    {
        if (!$args) return $select;
        list ($selector, $value, $config) = $args;
        if ($value == "first") {
            $select->where($this->currentAlias . ".rv_vg = 1");    
        } elseif ($value && $value != "last" && $value != 0) {
            $select->where($this->currentAlias . ".rv_vg = $value");    
        }
        return $select;        
    }
    
    /**
     * @param array $args
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderNameWhere($args, $select)
    {
        if (!$args) return $select;
        list ($selector, $value, $config) = $args;
        if ($config == "permissive")
            $select->where($this->currentAlias . ".name_vg LIKE '%$value%'");
        elseif ($config == "expression")
            $select->where($this->currentAlias . ".name_vg $value");
        else
            $select->where($this->currentAlias . ".name_vg = ?", $value);    
        return $select;        
    }
    
    /**
     * @param array $args
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderStatusWhere($args, $select)
    {
        if (!$args) return $select->where($this->currentAlias . ".status_vg = 'enabled'");
        
        list ($selector, $value, $config) = $args;
            
        if (count($this->store_revisionWhere) > 1) {
            $revision = $this->store_revisionWhere[1];
            if ($revision == "last") {
                $this->generateError("warning : revision is 'last' => status is either deleted or alive");
                $select->where($this->currentAlias . ".status_vg != 'disabled'");
                return $select;
            } else {
                // we do not need to set a status since the revision 
                // is already specified
                $this->generateError("warning : cannot set a specific revision and status.");
                return $select;
            }
        }
        
        if ($value == "trashed") {
            $select->where($this->currentAlias . ".status_vg = 'deleted'");
        } elseif ($value == "any") {
            $select->where($this->currentAlias . ".status_vg != 'disabled'");
        } else {
            $select->where($this->currentAlias . ".status_vg = 'enabled'");
        }
        return $select;  
    }
    
    /**
     * @param array $args
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderSecurityWhere($args, $select)
    {    
        if ($args) {
            
            list ($selector, $value, $config) = $args;
            if (!$value) $value = $this->api->getUser();
            $userId = $value instanceof Mira_Core_User ? $value->id : $value;
            if ($config == "editor")
                $select->from(array($this->currentAlias => self::generateAuthVegasSql($this->table, $userId, 'editor')), array("$this->currentAlias.*"));
            elseif ($config == "viewer")
                $select->from(array($this->currentAlias => self::generateAuthVegasSql($this->table, $userId, null)), array("$this->currentAlias.*"));
            elseif ($config == "viewer_only")
                $select->from(array($this->currentAlias => self::generateAuthVegasSql($this->table, $userId, 'viewer')), array("$this->currentAlias.*"));
            elseif ($config == "none") { 
                // @todo implement  
            }    
            return $select;
                    
        } else {
            
            if ($this->api->getUser()) {
                
                // by default we filter on user's vegas only
                $this->store_securityWhere = array(self::SELECTOR_SECURITY, null, "viewer");
                return $this->renderSecurityWhere($this->store_securityWhere, $select);
                
            } else {
                
                // if user not set, then we don't put any security lock
                $select->from(array($this->currentAlias => Mira_Core_Constants::TABLE_VEGA), array("$this->currentAlias.*"));
                return $select;
            }
        }
    }
    
    /**
     * @param array $args
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderTypeWhere($args, $select, $targetColumnName_id = "id_vgt_vg", $targetColumnName_rv = "rv_vgt_vg", $typeAlias = "vegatype_vgt")
    {    
        $args = $args ? $args : $this->store_typeWhere;
        
        $joinCondition = "$targetColumnName_id = $typeAlias.id_vgt" . ($targetColumnName_rv ? " AND $targetColumnName_rv = $typeAlias.rv_vgt" : "");
        $joinCols = array("$typeAlias.id_vgt", "$typeAlias.rv_vgt", "$typeAlias.name_vgt", "$typeAlias.fqn_vgt");
        
        if ($args) {
            list ($selector, $value, $config) = $args;
            if ($value instanceof Mira_Core_Select_VegaTypeSelect) {
                $select->joinInner(
                            array($typeAlias => $value->prepareSelect()), 
                        	$joinCondition, 
                            $joinCols); // @todo aliases also vgt
            } else {
                $select->joinInner(
                            array($typeAlias => Mira_Core_Constants::TABLE_VEGATYPE), 
                        	$joinCondition, 
                            $joinCols);
                if (Mira_Utils_String::isId($value)) {
                    $typeId = $value;
                    $select->where("$typeAlias.id_vgt = $typeId");
                } elseif ($value instanceof Mira_Core_VegaType) {
                    $typeId = $value->id;
                    $select->where("$typeAlias.id_vgt = $typeId");
                } else { // string => name
                    $select->where(self::generateStringWhere("$typeAlias.name_vgt", $value, $config));   
                }
            }    
            return $select;        
        } else {
            $select->joinInner(
                        array($typeAlias=>Mira_Core_Constants::TABLE_VEGATYPE), 
            			$joinCondition, 
                        $joinCols);
                        $andres = $select->__toString();
            return $select;
        }
    }
    
    /**
     * If the property is a primitive (i.e. not a link to a vega) then this function 
     * sets a simple filter on the current select. If it is a vega, then it converts it to 
     * a link that will be processed in {@link renderLinks()}
     * 
     * @param array $multipleArgs
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderPropertyWheres($multipleArgs, $select)
    {
        if (!$multipleArgs || !count($multipleArgs)) return $select;
        
        $extensionTableAlias = null;
        foreach($multipleArgs as $args) {
            
            list ($propertySelector, $value, $config) = $args;
            $propertyDetails = $this->prepare_details[$propertySelector];
            if (!$propertyDetails) {
                $this->generateError("error : selector $propertySelector did not match any existing property");
                return null;
            }
            list ($propertyId, $propertyName, $typeId, $typeRv, $isPrimitive, $propertyTypeId) = $propertyDetails;
            
            // unique id to identify this vega join
            $currentTableAlias = "property_" . uniqid();
            $extTableName = "vega_" . $typeId;
            
            if ($isPrimitive) { // primitives
                
                if (!$extensionTableAlias) {
                    $extensionTableAlias = $currentTableAlias;
                    $select->joinInner(
                        array("$extensionTableAlias" => $extTableName), 
                        "$extensionTableAlias.id_vg = $this->currentAlias.id_vg AND " . 
                        "$extensionTableAlias.rv_vg = $this->currentAlias.rv_vg",
                        ""
                        );
                }
                
                $select->where(self::generateStringWhere("$extensionTableAlias.$propertyId", $value, $config));
            
            } else { // vega property links
            
                $this->store_linkedTos[] = array($value, $propertySelector, "from", $config);
                
            }
        } 
        
        return $select;
    }
    
    
    /**
     * @param array $multipleArgs
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderLinks($multipleArgs, $select)
    {
        if (!$multipleArgs || !count($multipleArgs)) return $select;
        
        foreach ($multipleArgs as $arg) {
            
            list($value, $linkType, $linkDirection, $config) = $arg; 
            
            $currentTableAlias = "property_" . uniqid();
            $currentLinkAlias = "link_" . $currentTableAlias;
            
            $vegaPropertySelect = $this->table->select()
                    ->setIntegrityCheck(false)
                    ->from(array($currentLinkAlias => Mira_Core_Constants::TABLE_VEGALINK));
            
            if ($linkType == self::LINK_TYPE_GENERIC) {
                $vegaPropertySelect->joinInner(Mira_Core_Constants::TABLE_VEGALINKTYPE, "id_vlt = id_vlt_vgl", "")
                                   ->where("name_vlt = 'generic'"); 
            
            } elseif ($linkType == self::LINK_TYPE_ANY) {
                // do not put any filter
            
            } else { // that's a property
                
                
                $propertyDetails = $this->prepare_details[$linkType];
                if (!$propertyDetails) {
                    $this->generateError("error : selector $linkType did not match any existing property");
                    return null;
                }
                list ($propertyId, $propertyName, $typeId, $typeRv, $isPrimitive, $propertyTypeId) = $propertyDetails;
                
                $vegaPropertySelect->joinInner(Mira_Core_Constants::TABLE_VEGALINK_PROPERTY, "id_vgl = id_vgl_vlp");
                $vegaPropertySelect->where("id_prp_vlp = $propertyId");
            }
            
            $joinCondition = null;
            $joinCondition2 = null;
            if ($linkDirection == self::LINK_DIRECTION_FROM) {
                $joinCondition = "$currentLinkAlias.to_id_vg_vgl = $currentTableAlias.id_vg";
                $joinCondition2 = "$this->currentAlias.id_vg = $currentTableAlias.from_id_vg_vgl ".
                			      "AND $this->currentAlias.rv_vg = $currentTableAlias.from_rv_vg_vgl";
            } elseif ($linkDirection == self::LINK_DIRECTION_TO) {
                $joinCondition = "$currentLinkAlias.from_id_vg_vgl = $currentTableAlias.id_vg " .
                                 "AND $currentLinkAlias.from_rv_vg_vgl = $currentTableAlias.rv_vg";    
                $joinCondition2 = "$this->currentAlias.id_vg = $currentTableAlias.to_id_vg_vgl";
            } else { // any
                // not supported at the moment
                // $joinCondition = "to_id_vg_vgl = $currentTableAlias.id_vg OR from_id_vg_vgl = $currentTableAlias.id_vg AND from_rv_vg_vgl = $currentTableAlias.rv_vg";
                throw new Mira_Core_Exception_NotImplementedException("linkDirection = 'any' is not yet implemented");
                return null;    
            }
            
            if ($value instanceof Mira_Core_Vega) {
                $joinCondition .= " AND $currentTableAlias.id_vg = $value->id AND $currentTableAlias.rv_vg = $value->revision";
            } elseif (!($value instanceof Mira_Core_Select_VegaSelect)) {
                $joinCondition .= " AND " . self::generateComplexCondition("$currentTableAlias.id_vg", "$currentTableAlias.name_vg", $value, $config);
            } 
                    
            if ($value instanceof Mira_Core_Select_VegaSelect) {
                $vegaPropertySelect->joinInner(array($currentTableAlias => $value->prepareSelect($currentTableAlias)), $joinCondition, "");
            } else {
                $vegaPropertySelect->joinInner(array($currentTableAlias => Mira_Core_Constants::TABLE_VEGA), $joinCondition, "");
            }
            
            $select->joinInner(array($currentTableAlias => $vegaPropertySelect), 
            	$joinCondition2, "");
        }
        
        return $select;
    }
    
    /**
     * 
     * @param array $options
     * @param Zend_Db_Adapter_Abstract $select
     * @return Zend_Db_Adapter_Abstract
     */
    protected function renderOptions ($options, $select)
    {
        $offset = Mira_Utils_OptionsHelper::getOption("offset", 0, $options);
        $count = Mira_Utils_OptionsHelper::getOption("count", 50, $options);
        $orderBy = Mira_Utils_OptionsHelper::getOption("orderBy", "name", $options);
        $ascending = Mira_Utils_OptionsHelper::getOption("orderAsc", true, $options);
        
        $select->limit($count, $offset);
        $orderBy = $this->getColumnName($orderBy);
        $select->order($orderBy . " " . ($ascending ? "ASC" : "DESC"));
        
        return $select;
    }
    
    // ###################################################
    // PRIVATE
    // ###################################################
    
    /**
     * @access private
     * @param array $multipleArgs = array of (propertyId | propertyName, propertyValue, config)
     * @param Zend_Db_TableSelect $select
     * @returns array of (propertyId, propertyValue, config, (propertyId, propertyName, vegaTypeId, vegaTypeRv))
     */
    private function retrievePropertyDetails($multipleArgs, $select)
    {
        $select->joinInner(Mira_Core_Constants::TABLE_VEGAPROPERTY, "id_vgt_prp = id_vgt AND rv_vgt_prp = rv_vgt");
        $whereStrings = array();
        foreach ($multipleArgs as $pWhereArgs) {
            list ($selector, $value, $config) = $pWhereArgs;
            $whereStrings[] = Mira_Utils_String::isId($selector) ? "id_prp = $selector" : "name_prp = '$selector'";    
        }
        $select->where("(" . implode(" OR ", $whereStrings) . ")");
        $select->group("id_prp");
        $rs = $select->query(Zend_Db::FETCH_ASSOC)->fetchAll();
        
        // index the results
        $propertyDetailsByName = array();
        $propertyDetailsById= array();
        $firstTypeId = 0;
        $firstTypeRv = 0;
        foreach ($rs as $row) {
            $name = $row["name_prp"];
            $id = $row["id_prp"];
            if (isset($propertyDetailsByName[$name])) {
                // @todo or make union ?
                $this->generateError("warning : $name is ambiguous. refine using vegaType selector.");
            } else {
                if (!$firstTypeId) {
                    $firstTypeId = $row["id_vgt_prp"];
                    $firstTypeRv = $row["rv_vgt_prp"];
                } elseif ($firstTypeId != $row["id_vgt_prp"]) {
                    $this->generateError("error : property selectors are not coherent (multiple matching vegaTypes encountered)");
                    return false;
                }
                $propertyDetailsByName[$name] =     array($id, $name, $row["id_vgt_prp"], $row["rv_vgt_prp"], is_null($row["type_id_vgt_prp"]));
                $propertyDetailsById[$id] =         array($id, $name, $row["id_vgt_prp"], $row["rv_vgt_prp"], is_null($row["type_id_vgt_prp"]));
            }
        }
        
        // process the input to enrich it (with type / name / id ...)
        $newArgs = array();
        foreach ($multipleArgs as $pWhereArgs) {
            list ($selector, $value, $config) = $pWhereArgs;
            $propertyDetails = Mira_Utils_String::isId($selector) ? $propertyDetailsById[$selector] : $propertyDetailsByName[$selector];
            if ($propertyDetails) {
                $newArgs[] = array($propertyDetails[0], $value, $config, $propertyDetails);
            } else {
                $this->generateError("error : no property $selector found in results.");
                return false;
            }
        }
        
        return $newArgs;
    }
    
    /**
     * @access private
     */
    private function getColumnName($prettyName) 
    {
        if ($prettyName == "name") return "name_vg";
        if ($prettyName == "creationDate") return "date_created_vg";
        return $prettyName;
    }
    
    // ###################################################
    // UTILS
    // ###################################################
    
     /**
     * @access private
     * @param integer $userId
     * @param string $role if null then either 'editor' or 'viewer'
     * @param string $asPrefix prefixes to the aliases
     * @return Zend_Db_Table_Select
     */
    private static function generateAuthVegasSql ($vegaTable, $userId, $role = null, $aliasPrefix = '')
    {
        $retSel = $vegaTable->select()->setIntegrityCheck(false);
        $VG = Mira_Core_Constants::TABLE_VEGA;
        $SC = Mira_Core_Constants::TABLE_SCOPE_CUSTOM;
        $retSel->from($VG, "vega_vg.*");
        $retSel->joinLeft($SC, "id_scp_vg = id_scp_scc", "");
        
        $roleWhere = " ";
        if ($role) $roleWhere = " AND role_scc = '$role'";
        $retSel->where("(id_usr_scc = $userId $roleWhere) OR (id_usr_scc = " . Mira_Core_Scope::PUBLIC_USERID . " $roleWhere) OR id_usr_vg = 0");
        $retSel->group(array("id_vg", "rv_vg"));
        
        return $retSel;
    }
    
    /**
     * @access private
     * @param integer $idSelector
     * @param string $stringSelector
     * @param string $value
     * @param string $config
     */
    private static function generateComplexCondition($idSelector, $stringSelector, $value, $config)
    {
        if (Mira_Utils_String::isId($value)) {
            return "$idSelector = $value";
        } elseif (is_string($value)) {
            return self::generateStringWhere($stringSelector, $value, $config);
        } elseif (is_object($value)) {
            return "$idSelector = $value->id";
        } else {
            return "false";
        }
                
    }
}