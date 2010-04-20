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
 * @category   Mira
 * @package    Mira_Core
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Constants
{
    // table names
    // @access private
    const TABLE_VEGA              = 'vega_vg';
    // @access private
    const TABLE_VEGATYPE          = 'vegatype_vgt';
    // @access private
    const TABLE_VEGALINK          = 'vegalink_vgl';    
    // @access private
    const TABLE_VEGALINKTYPE      = 'vegalinktype_vlt';    
    // @access private
    const TABLE_VEGALINK_PROPERTY = 'vegalink_property_vlp';
    // @access private
    const TABLE_VEGAPROPERTY      = 'vegaproperty_prp';
    // @access private
    const TABLE_SCOPE             = 'scope_scp';
    // @access private
    const TABLE_SCOPE_CUSTOM      = 'scope_custom_scc';
    // @access private
    const TABLE_USER              = 'user_usr';
    
    // registry values (stored in Zend_Registry)
    const REG_API                 = "mira::api";    
    const REG_DBADAPTER           = "mira::dbAdapter";    
    const REG_ROOT                = "mira::root";    
    const REG_FILESPATH           = "mira::filesPath";    
    const REG_CONFIG              = "mira::config";    
    const REG_LOG                 = "mira::log";    
    const REG_BUS                 = "mira::bus";    
    const REG_LUCENEINDEXPATH     = "mira::luceneIndexPath";    
}
