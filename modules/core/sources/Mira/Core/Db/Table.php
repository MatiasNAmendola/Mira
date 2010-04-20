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
 * Mira tables have the sql suffix convention (3 letter identifiers as suffixes).
 * This class enables quick find by id.
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Db
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Db_Table extends Zend_Db_Table
{
    protected $_suffix = '';

    public function __construct ($name, $rowClass, $sequence, $tableSuffix)
    {
        if (!$rowClass) $rowClass = "Zend_Db_Table_Row";
        parent::__construct(array("name" => $name , "rowClass" => $rowClass , "sequence" => $sequence));
        $this->_suffix = $tableSuffix;
    }

    public function findById ($id)
    {
        $ret = $this->fetchRow("id_" . $this->_suffix . " = " . $id);
        return $ret;
    }
}