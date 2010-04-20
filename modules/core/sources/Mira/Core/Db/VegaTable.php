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
 * Special table for Vegas that enables dynamic row classes depending on type 
 * of vegas.
 * 
 * {@link Mira_Core_Vega}
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Db
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Db_VegaTable extends Mira_Core_Db_Table
{
	protected $_rowsetClass = "Mira_Core_Db_VegaRowset";
    
    /**
     * Fetches one row in an object of type Zend_Db_Table_Row_Abstract,
     * or returns null if no row matches the specified criteria.
     *
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @return Zend_Db_Table_Row_Abstract|null The row results per the
     *     Zend_Db_Adapter fetch mode, or null if no row found.
     */
    public function fetchRow($where = null, $order = null)
    {
        if (!($where instanceof Zend_Db_Table_Select)) {
            $select = $this->select();

            if ($where !== null) {
                $this->_where($select, $where);
            }

            if ($order !== null) {
                $this->_order($select, $order);
            }

            $select->limit(1);

        } else {
            $select = $where->limit(1);
        }

        $rows = $this->_fetch($select);

        if (count($rows) == 0) {
            return null;
        }

        $data = array(
            'table'   => $this,
            'data'     => $rows[0],
            'readOnly' => $select->isReadOnly(),
            'stored'  => true
        );
		
        // Try to return the type written in fqn of the type of the vega
        $fqn = null;
        if ($this->_rowClass == "Mira_Core_Vega") {
            $fqn = $data["data"]["fqn_vgt"];
        }
    	$rowClass = $this->getRowClassEx($fqn);
        return new $rowClass($data);
    }

    public function createRowEx($vegaType) 
    {
        // this function should not be multithreaded
        $rc = $this->getRowClass();
        $this->setRowClass($this->getRowClassEx($vegaType->fqn));
        $ret = parent::createRow();
        $this->setRowClass($rc);
        return $ret;
    }
    
    public function getRowClassEx($fqn) 
    {
    	if ($fqn && $this->autoload($fqn)) 
            return $fqn;
        else
            return parent::getRowClass();
    }
    
    /**
     * @access private
     */
    private function autoload($class)
    {
        if (!isset($class)) return false;
        try {
            /**
             * @see Mira_Utils_Loader
             */
            require_once 'Mira/Utils/Loader.php';
            Mira_Utils_Loader::loadClass($class, null, true);
            return true;
        } catch (Zend_Exception $e) {
            return false;
        }
    }
}
