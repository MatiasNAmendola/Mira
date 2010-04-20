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
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * This is a simple VO (value object) that can represent any object, without 
 * any overhead - but this won't contain advanced features surch as security
 * or versionning
 * 
 * @category   Mira
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Reference
{
    /**
     * this is used by AMF to specify remote AS classname
     * @access private
     * @var string
     */
    public $_explicitType = "com.vega.core.api.vega.Reference";
    
    // @var integer
	public $id;
	
	// @var string
	public $uid;
	
	// @var string
	public $name;
	
	// @var string
	public $type;
	
	// @var array
	public $meta;
	
	public function addMeta($key, $value) 
	{
	    if (!isset($this->meta)) {
	        $this->meta = array();
	    }
	    $this->meta[$key] = $value;
	}
}
?>