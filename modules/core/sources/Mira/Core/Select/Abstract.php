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
 * Core logic for selects on Mira API.
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Select
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
abstract class Mira_Core_Select_Abstract
{
    const SELECTOR_NAME = "name";
    const SELECTOR_ID = "id";
    const SELECTOR_REVISION = "revision";
    const SELECTOR_STATUS = "status";
    const SELECTOR_SECURITY = "security";
    
    /* @var Zend_Db_Table */
    protected $table;
    protected $currentAlias;
    
    protected $api;
    
    /**
     * @param Mira $api 
     * @param Zend_Db_Table $table
     */
    public function __construct($api, $table)
    {
        $this->api = $api;
        $this->table = $table; 
    }
    
    /**
     * Add filters to the current query
     * 
     * Available selectors:
     * <ol>
     * <li>
     * 	<ul>
     * 		<li>$selector: {@link SELECTOR_NAME}</li>
     * 		<li>$value: any string | expression</li>
     * 		<li>$config: "strict" (default) | "permissive" | "expression"</li>
     * 	</ul>
     * </li>
     * <li>
     * 	<ul>
     * 		<li>$selector: {@link SELECTOR_ID}</li>
     * 		<li>$value: any integer</li>
     * 	</ul>
     * </li>
     * <li>
     * 	<ul>
     * 		<li>$selector: {@link SELECTOR_REVISION}</li>
     * 		<li>$value: any integer | "first" | "last" (default)</li>
     * 	</ul>
     * </li>
     * <li>
     * 	<ul>
     * 		<li>$selector: {@link SELECTOR_STATUS}</li>
     * 		<li>$value: "alive" (default) | "trashed" | "any"</li>
     * 	</ul>
     * </li>
     * <li>
     * 	<ul>
     * 		<li>$selector: {@link SELECTOR_SECURITY}</li>
     * 		<li>$value: Mira_Core_User | user id</li>
     * 		<li>$config: "viewer" (default) | "editor" | "viewer_only" | "none"</li>
     * 		<li>note: <b>This filter can be used when api auth level is application or system</b></li>
     * 	</ul>
     * </li>
     * </ol>
     * 
     * @param string $selector
     * @param mixed $value
     * @param mixed $config
	 * @return Mira_Core_Select_Abstract;
     */
    abstract public function where($selector, $value = null, $config = null);
    
    /**
     * Sets a limit count and offset to the query.
     *
     * @param integer $offset
     * @param integer $count
	 * @return Mira_Core_Select_Abstract;
     */
    abstract public function limit($count = 50, $offset = 0);
    
    /**
     * Sets an order to the query.
     *
     * @param string $byProperty
     * @param boolean $ascending
	 * @return Mira_Core_Select_Abstract;
     */
    abstract public function order($byProperty, $ascending = true);
    
    /**
     * This is the main orchestror of the select interpretation. It converts this Select
     * to a Zend_Db_Select that can be queried against the table.
     * 
     * In case we want to embed this select into another one, we often
     * have to provide a unique alias to avoid conflicts. Use $alias to set this.
     * 
     * @param string $alias
     */
    protected function prepareSelect($alias = null)
    {
        $this->currentAlias = $alias;
        
        // trunk selections
        // @var Zend_Db_Table_Select
        $select = new Zend_Db_Table_Select($this->table);
        $select->setIntegrityCheck(false);

        return $select;
    }
    
    /**
     * Used when fetching as lazy. This has to return a simple {@link Mira_Core_Reference}
     * instead of the fully fledged typed object.
     * 
     * @param Zend_Db_Table_Row $row
     */
    abstract protected function buildReferenceFromRow($row);

    // ###################################################
    // FETCHERS
    // ###################################################
    
    /**
     * Simple count on objects selected.
     * 
     * @return integer
     */
    public function count()
    {
        $innerSelect = $this->prepareSelect();
        if (!$innerSelect) return 0;
        
        $db = $this->table->getAdapter();
        
        $wrappingSelect = new Zend_Db_Select($db);
        $wrappingSelect->from($innerSelect, new Zend_Db_Expr("COUNT(*)"))
                       ->limit(1);
                       
        return intval($db->fetchOne($wrappingSelect));
    }
    
    /**
     * Fetches one object.
     * 
     * @param $lazy 
     * @return Mira_Core_Reference if $lazy is true otherwise the corresponding class (Mira_Core_Vega/User or VegaType)
     */
    public function fetchObject($lazy = false)
    {
        $select = $this->prepareSelect();
        if (!$select) return null;
        if ($lazy) {
            $rows = $select->limit(1)
                        ->query(Zend_Db::FETCH_ASSOC)
                        ->fetchAll();
            if (count($rows)>0) 
                return $this->buildReferenceFromRow($rows[0]);
            else 
                return null; 
        } else {
            return $this->table->fetchRow($select);
        }
    }
    
    /**
     * Fetches an array of objects.
     * 
     * @param $lazy
     * @return array of Mira_Core_Reference if $lazy is true otherwise the corresponding class (Mira_Core_Vega/User or VegaType)
     */
    public function fetchAll($lazy = false)
    {
        $select = $this->prepareSelect();
        if (!$select) return null;
        $ret = array();
        if ($lazy) {
            $results = $select->query(Zend_Db::FETCH_ASSOC)->fetchAll();
            foreach ($results as $row) {
                $ret[] = $this->buildReferenceFromRow($row);
            }
        } else {
            return Mira_Utils_Db::rowsetToArray($this->table->fetchAll($select));
        }
        return $ret;
    }
    
    /**
     * @return the sql string representation of this Select
     */
    public function __toString()
    {
        return $this->prepareSelect()->__toString();
    }
    
    // ###################################################
    // UTILS
    // ###################################################
    
    /**
     * @param string $message
     */
    protected function generateError($message)
    {
        // print ("\n\n ** ERROR ** \n$message");
    }

    /**
     * @param string $selector
     * @param string $stringValue
     * @param string $config "permissive" | "expression" | "strict"
     */
    protected static function generateStringWhere($selector, $stringValue, $config)
    {
        if ($config == "permissive") {
            return "$selector LIKE '%$stringValue%'";
        } elseif ($config == "expression") {
            return "$selector $stringValue";
        } else {
            return "$selector = '$stringValue'";
        }
    }
}