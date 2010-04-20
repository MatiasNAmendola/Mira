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
 * This rowset class enables dynamic rowclasses in Zend_Db_Table
 * 
 * {@link Mira_Core_Db_VegaTable}
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Db
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Db_VegaRowset extends Zend_Db_Table_Rowset_Abstract {
	
	/**
     * Return the current element.
     * Similar to the current() function for arrays in PHP
     * Required by interface Iterator.
     *
     * @return Zend_Db_Table_Row_Abstract current element from the collection
     */
    public function current()
    {
        if ($this->valid() === false) {
            return null;
        }

        // do we already have a row object for this position?
        if (empty($this->_rows[$this->_pointer])) {
            $data = array(
                    'table'    => $this->_table,
                    'data'     => $this->_data[$this->_pointer],
                    'stored'   => $this->_stored,
                    'readOnly' => $this->_readOnly
                );
            $fqn = null;
            if ($this->_table->getRowClass() == "Mira_Core_Vega")
                $fqn = $data["data"]["fqn_vgt"];
            $rowClass = $this->_table->getRowClassEx($fqn); 
            $this->_rows[$this->_pointer] = new $rowClass($data);
        }

        // return the row object
        return $this->_rows[$this->_pointer];
    }
}

?>