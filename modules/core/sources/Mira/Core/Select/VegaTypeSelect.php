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
class Mira_Core_Select_VegaTypeSelect extends Mira_Core_Select_Abstract
{
    const SELECTOR_FQN = "fqn";
    
    protected $table;
    
    protected $store_idWhere;
    protected $store_revisionWhere;
    protected $store_nameWhere;    
    protected $store_statusWhere;
    protected $store_fqnWhere;
    protected $store_securityWhere;
    protected $store_options = array();
    
    /**
     * {@inheritdoc}
     * Mira_Core_Select_VegaSelect special selectors:
     * <ol>
     * <li>
     * 	<ul>
     * 		<li>$selector: {@link SELECTOR_FQN}</li>
     * 		<li>$value: any string</li>
     * 		<li>$config: "strict" (default) | "permissive" | "expression"</li>
     * 	</ul>
     * </li>
     * </ol>
     * 
     * 
     * @param string|id $selector
     * @param mixed $value
     * @param mixed $config
     * @return Mira_Core_Select_VegaTypeSelect
     */
    public function where($selector, $value = null, $config = null) 
    {
        $args = array($selector, $value, $config);
        switch ($selector) {
            case self::SELECTOR_NAME:
                $this->store_nameWhere = $args;
                break;
            case self::SELECTOR_ID:
                $this->store_idWhere = $args;
                break;
            case self::SELECTOR_REVISION:
                $this->store_revisionWhere = $args;
                break;
            case self::SELECTOR_STATUS:
                $this->store_statusWhere = $args;
                break;
            case self::SELECTOR_SECURITY:
                $this->store_securityWhere = $args;
                break;
            case self::SELECTOR_FQN:
                $this->store_fqnWhere = $args;
                break;
        }
        return $this;
    }
    
    /**
     * {@inheritdoc}
     * @param integer $offset
     * @param integer $count
     * @return Mira_Core_Select_VegaTypeSelect
     */
    public function limit($count = 50, $offset = 0)
    {
        $this->store_options["offset"] = $offset;
        $this->store_options["count"] = $count;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     * @param string $byProperty
     * @param boolean $ascending
     * @return Mira_Core_Select_VegaTypeSelect
     */
    public function order($byProperty, $ascending = true) 
    {
        $this->store_options["orderBy"] = $byProperty;
        $this->store_options["orderAsc"] = $ascending;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     * @return Zend_Db_Table_Select
     */
    protected function prepareSelect($alias = null)
    {
        // @var Zend_Db_Table_Select
        $select = parent::prepareSelect();

        // all renders
        $select = $this->renderIdWhere($this->store_idWhere, $select);
        $select = $this->renderNameWhere($this->store_nameWhere, $select);
        $select = $this->renderRevisionWhere($this->store_revisionWhere, $select);
        $select = $this->renderSecurityWhere($this->store_securityWhere, $select);
        $select = $this->renderFqnWhere($this->store_fqnWhere, $select);
        $select = $this->renderStatusWhere($this->store_statusWhere, $select);
        
        if ($select) $select = $this->renderOptions($this->store_options, $select);
        
        return $select;
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
        $ret->id = $row["id_vgt"];
        $ret->uid = Mira_Core_VegaType::getUID($row["id_vgt"], $row["rv_vgt"]);
        $ret->name = $row["name_vgt"];
        $ret->type = "vegaType";
        $ret->addMeta("ownerId", $row["id_usr_vgt"]);
        $ret->addMeta("creationDate", $row["date_created_vgt"]);
        $ret->addMeta("revision", $row["rv_vgt"]);
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
        $select->where(Mira_Core_Constants::TABLE_VEGATYPE . ".id_vgt = $value");    
        return $select;        
    }
    
    /**
     * @param array $args
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderFqnWhere($args, $select)
    {
        if (!$args) return $select;
        list ($selector, $value, $config) = $args;
        if ($config == "permissive")
            $select->where(Mira_Core_Constants::TABLE_VEGATYPE . ".fqn_vgt LIKE '%$value%'");
        elseif ($config == "expression")
            $select->where(Mira_Core_Constants::TABLE_VEGATYPE . ".fqn_vgt $value");
        else
            $select->where(Mira_Core_Constants::TABLE_VEGATYPE . ".fqn_vgt = ?", $value);      
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
            $select->where(Mira_Core_Constants::TABLE_VEGATYPE . ".rv_vgt = 1");    
        } elseif ($value && $value != "last" && $value != 0) {
            $select->where(Mira_Core_Constants::TABLE_VEGATYPE . ".rv_vgt = $value");    
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
            $select->where(Mira_Core_Constants::TABLE_VEGATYPE . ".name_vgt LIKE '%$value%'");
        elseif ($config == "expression")
            $select->where(Mira_Core_Constants::TABLE_VEGATYPE . ".name_vgt $value");
        else
            $select->where(Mira_Core_Constants::TABLE_VEGATYPE . ".name_vgt = ?", $value);    
        return $select;        
    }
    
    /**
     * @param array $args
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderStatusWhere($args, $select)
    {
        if (!$args) {
            
            $select->where(Mira_Core_Constants::TABLE_VEGATYPE . ".status_vgt = 'enabled'");
            return $select;
            
        } else {
            
            list ($selector, $value, $config) = $args;
            
            if (count($this->store_revisionWhere) > 1) {
    
                $revision = $this->store_revisionWhere[1];
                
                if ($revision == "last") {
                    
                    $this->generateError("warning : revision is 'last' => status is either deleted or alive");
                    $select->where(Mira_Core_Constants::TABLE_VEGATYPE . ".status_vgt != 'disabled'");
                    return $select;
                    
                } else {
                    // we do not need to set a status since the revision 
                    // is already specified
                    $this->generateError("warning : cannot set a specific revision and status.");
                    return $select;
                }
                
            }
            
            if ($value == "trashed") {
                $select->where(Mira_Core_Constants::TABLE_VEGATYPE . ".status_vgt = 'deleted'");
            } elseif ($value == "any") {
                $select->where(Mira_Core_Constants::TABLE_VEGATYPE . ".status_vgt != 'disabled'");
            } else {
                $select->where(Mira_Core_Constants::TABLE_VEGATYPE . ".status_vgt = 'enabled'");
            }
            return $select;  
        }      
    }
    
    /**
     * @param array $args
     * @param Zend_Db_Table_Select $select
     * @return Zend_Db_Table_Select
     */
    protected function renderSecurityWhere($args, $select)
    {
        if ($args)
            $this->generateError("warning : security is not yet implemented on vegaTypes");
        $select->from(Mira_Core_Constants::TABLE_VEGATYPE);
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
     * @param unknown_type $prettyName
     */
    private function getColumnName($prettyName) 
    {
        if ($prettyName == "name") return "name_vgt";
        if ($prettyName == "creationDate") return "date_created_vgt";
        return $prettyName;
    }
    
    // ###################################################
    // UTILS
    // ###################################################
    
}