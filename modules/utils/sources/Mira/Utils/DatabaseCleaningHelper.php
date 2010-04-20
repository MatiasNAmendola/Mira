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
 * @package    Mira_Utils
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @category   Mira
 * @package    Mira_Utils
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Utils_DatabaseCleaningHelper
{
    public function clean($dbAdapter, $sqlFile) 
    {
        // read the file
        $file_handle = fopen($sqlFile, "r");
        $string = '';
        while (! feof($file_handle)) {
            $string = $string . fgetss($file_handle);
        }
        fclose($file_handle);
        
        // empty all database
        if ($dbAdapter instanceof Zend_Db_Adapter_Pdo_Pgsql) {
        	$rowset = $dbAdapter->fetchAll("select * from information_schema.tables where table_schema='public' and table_type='BASE TABLE';", array(), Zend_Db::FETCH_NUM);
        	$sqlTemplate = "DROP TABLE %s CASCADE";
        	$nbRowNameTable = "2";
        } else {
        	$rowset = $dbAdapter->fetchAll("SHOW TABLES", array(), Zend_Db::FETCH_NUM);
        	$sqlTemplate = "DROP TABLE `%s`";
        	$nbRowNameTable = "0";
        }
        
        foreach ($rowset as $row) {
            $sql = sprintf($sqlTemplate, $row[$nbRowNameTable]);
            // @non_sql_agnostic
            $dbAdapter->query($sql);
        }
        
        // re-create the structure
        try {
            $dbAdapter->query($string);
        } catch (Zend_Exception $e) {
            print('error recreating sql structure\n');
        }
        $dbAdapter->closeConnection();
    }
}
?>