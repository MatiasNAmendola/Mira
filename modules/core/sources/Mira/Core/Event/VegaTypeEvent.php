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
 * @subpackage Event
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * Dispatched on the bus on specific occasions (vegatype just edited, just created... etc)
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Event
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Event_VegaTypeEvent extends Mira_Utils_Event
{
    const EDIT = "vegatype_edit";
    const CREATE = "vegatype_create";
    const DELETE = "vegatype_delete";
    const RESTORE = "vegatype_restore";
    const ROLLBACK = "vegatype_rollback";
    
    /** @var Mira_Core_VegaType */
    public $vegaType;
    
    public $related;
    
    public function __construct($name, $vegaType, $related = null)
    {
        parent::__construct($name, null);
        $this->vegaType = $vegaType;
        $this->related = $related;    
    }
}
?>