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
 * @subpackage Pretty
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @category   Mira
 * @package    Mira_Utils
 * @subpackage Pretty
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Utils_Pretty_Property_ForeignTableRow extends Mira_Utils_Pretty_Property_Abstract 
{
	public $localKeyColumn;
	
	public $foreignTable;
	public $foreignKeyColumn;
	
	public function __construct($prettyName, $useCache = false, $localKeyColumn, $foreignTable, $foreignKeyColumn) 
	{
		parent::__construct($prettyName, $useCache);
		
		if (isset($localKeyColumn) && isset($foreignTable)) {
			$this->localKeyColumn = $localKeyColumn;
			$this->foreignTable = $foreignTable;
			$this->foreignKeyColumn = $foreignKeyColumn;
		} else {
			throw new Exception("Missing parameter (local column or foreign table)");
		}
	}
	
	public function getValue($target) 
	{
		// @todo check that $target is an actual Table_Row...
		
		$id = $target[$this->localKeyColumn];
		return $this->foreignTable->findById($id);
	}
	
	public function setValue($target, $value) 
	{
		if ($value == null) {
			$target[$this->localKeyColumn] = null;
		} else if (isset($this->foreignKeyColumn)) {
			$target[$this->localKeyColumn] = $value[$this->foreignKeyColumn];
		} else {
			throw new Exception("Cannot set value $this->prettyName, foreignKeyColumn was not set.");
		}
	}
}
