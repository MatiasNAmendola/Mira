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
class Mira_Core_VegaLink
{

    /**
     * this is used by AMF to specify remote AS classname
     * @access private
     * @var string
     */
    public $_explicitType = "com.vega.core.api.vega.VegaLink";
    
    // @var integer
    public $id;
    
    // @var integer
    public $linkTypeId;
    
    // @var Mira_Core_Reference
    public $fromVega;
    
    // @var Mira_Core_Reference
    public $toVega;
    
    // @var array
    public $meta;
    
    /**
	 * @param Mira_Core_Property $row
     * @return Mira_Core_VegaLink 
     */
    static public function createFromRow($row) 
    {
        $ret = new Mira_Core_VegaLink();
        $ret->id = $row->id_vgl;
        $ret->linkTypeId = $row->id_vlt;
        $ret->meta = array();
        if (intval($ret->linkTypeId) == 1) {
            $ret->meta["propertyName"] = $row->name_prp;  
            $ret->meta["propertyId"] = $row->id_prp;
        }
        
        // from vega
        $fromVega = new Mira_Core_Reference();
        $fromVega->id = $row->FV_id_vg;
        $fromVega->uid = Mira_Core_Vega::getUID($row->FV_id_vg, $row->FV_rv_vg);
        $fromVega->name = $row->FV_name_vg;
        $fromVega->type = "vega";
        $fromVega->addMeta("ownerId", $row->FV_id_usr_vg);
        $fromVega->addMeta("creationDate", $row->FV_date_created_vg);
        $fromVega->addMeta("revision", $row->FV_rv_vg);
//        $fromVega->addMeta("typeName", $row->FVT_name_vgt);
//        $fromVega->addMeta("typeId", $row->FVT_id_vgt);
        $ret->fromVega = $fromVega;
        
        // to vega
        $toVega = new Mira_Core_Reference();
        $toVega->id = $row->TV_id_vg;
        $toVega->uid = Mira_Core_Vega::getUID($row->TV_id_vg, $row->TV_rv_vg);
        $toVega->name = $row->TV_name_vg;
        $toVega->type = "vega";
        $toVega->addMeta("ownerId", $row->TV_id_usr_vg);
        $toVega->addMeta("creationDate", $row->TV_date_created_vg);
        $toVega->addMeta("revision", $row->TV_rv_vg);
//        $toVega->addMeta("typeName", $row->TVT_name_vgt);
//        $toVega->addMeta("typeId", $row->TVT_id_vgt);
        $ret->toVega = $toVega;
         
        return $ret;
    }
}