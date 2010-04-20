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
 * Dispatched on the bus on specific occasions (vega just edited, just created... etc)
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Event
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Event_VegaEvent extends Mira_Utils_Event
{
    const EDIT = "vega_edit";
    const CREATE = "vega_create";
    const DELETE = "vega_delete";
    const RESTORE = "vega_restore";
    const ROLLBACK = "vega_rollback";
    
    /** @var Mira_Core_Vega */
    public $vega;
    
    public $related;
    
    public function __construct($name, $vega, $related = null)
    {
        parent::__construct($name, null);
        $this->vega = $vega;
        $this->related = $related;    
    }
}
?>