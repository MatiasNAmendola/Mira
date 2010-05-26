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
 * @subpackage Db
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * Simple facade to access all tables used by Mira.
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Db
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Db_Tables
{
    
    public static $_instance = null;
    
    protected $_tables;
    
    /**
     * @return Mira_Core_Db_Tables
     */
    public static function getInstance ()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return (self::$_instance);
    }
    
    /**
     * @return Mira_Core_Db_VegaTable
     */
    public function getVegaTable()
    {
        if (!isset($this->_tables["vega"])) {
            $this->_tables["vega"] = new Mira_Core_Db_VegaTable('vega_vg', 'Mira_Core_Vega', true, 'vg');
        }
        return $this->_tables["vega"];
    }
    
    /**
     * @return Mira_Core_Db_Table
     */
    public function getVegaTypeTable()
    {
        if (!isset($this->_tables["vegaType"])) {
            $this->_tables["vegaType"] = new Mira_Core_Db_Table('vegatype_vgt', 'Mira_Core_VegaType', true, 'vgt');
        }
        return $this->_tables["vegaType"];
    }
    
    /**
     * @return Mira_Core_Db_Table
     */
    public function getUserTable()
    {
        if (!isset($this->_tables["user"])) {
            $this->_tables["user"] = new Mira_Core_Db_Table('user_usr', 'Mira_Core_User', true, 'usr');
        }
        return $this->_tables["user"];
    }
    
    /**
     * @return Mira_Core_Db_Table
     */
    public function getScopeTable()
    {
        if (!isset($this->_tables["scope"])) {
            $this->_tables["scope"] = new Mira_Core_Db_Table('scope_scp', 'Mira_Core_Scope', true, 'scp');
        }
        return $this->_tables["scope"];
    }
    
    /**
     * @return Mira_Core_Db_Table
     */
    public function getVegaLinkTable()
    {
        if (!isset($this->_tables["vegaLink"])) {
            $this->_tables["vegaLink"] = new Mira_Core_Db_Table('vegalink_vgl', null, true, 'vgl');
        }
        return $this->_tables["vegaLink"];
    }
    
    /**
     * @return Mira_Core_Db_Table
     */
    public function getVegaPropertyTable()
    {
        if (!isset($this->_tables["vegaProperty"])) {
            $this->_tables["vegaProperty"] = new Mira_Core_Db_Table('vegaproperty_prp', 'Mira_Core_Property', true, 'prp');
        }
        return $this->_tables["vegaProperty"];
    }
}
