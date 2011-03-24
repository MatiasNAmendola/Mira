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
class Mira_Core_Contact extends Mira_Core_Vega
{
    /**
     * @return Mira_Core_User
     */
    public function getUser()
    {
        $vegaId = $this->id;
        $table = Mira_Core_Db_Tables::getInstance()->getUserTable();
        return $table->fetchRow("id_vg_usr = $vegaId", null, 1);
    }
    
    /**
     * @return string concatenation of full Name - "Mathieu Lemaire"
     */
    public function getFullname()
    {
    	$f = $this->__get("full name");
    	if (Mira_Utils_String::isEmpty($f)) {
    		return $this->getUser()->pseudo_usr;
    	} else {
            return  $f;
        }
    }
    
    private function getPseudoFromEmail()
    {
    	$pos = strpos($this->email, "@");
    	return substr($this->email, 0, $pos);
    }
}
