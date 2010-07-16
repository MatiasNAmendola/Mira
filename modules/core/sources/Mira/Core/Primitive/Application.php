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
 * @package    Mira_File
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @category   Mira
 * @package    Mira_File
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Primitive_Application extends Mira_Core_Application_Abstract 
{
    static protected $allPrimitives;
    
    public function __construct()
    {
    }

    // ############################################
    // Application implementation
    // ############################################
    
	public function install()
	{
	    // nada to do
	}
	
	public function isInstalled()
	{
	    return true;
	}
	
	public function uninstall() {} // @todo
	
	public function start() 
	{
	    $allPrms = array();
	    
	    $envConf = Zend_Registry::get(Mira_Core_Constants::REG_CONFIG);
	    if (isset($envConf->app->list->config)) {
	        $configPath = MIRA_ROOT . "/" . $envConf->app->primitive->config;
	    } else {
	        $configPath = MIRA_ROOT . "/application/resources/app.primitive.conf";
	    }
        $config = new Zend_Config_Ini($configPath, Mira::getEnv());
        
	    $prims = $config->toArray();
	    foreach ($prims as $key => $prim) {
            $allPrms[] = $this->parsePrimitive($key, $prim);	        
	    }
	    
	    self::$allPrimitives = $allPrms;
	}
	
	public function stop() {}

	
    // ############################################
    // Public
    // ############################################

	public function findById($id)
	{
	    foreach (self::$allPrimitives as $prm) {
	        if ($prm->id == $id) return $prm;
	    }
	}
	
	public function findByName($name)
	{
	    foreach (self::$allPrimitives as $prm) {
	        if ($prm->name == $name) return $prm;
	        return $prm;
	    }
	}
	
	public function getAll($ignoreInternal = false)
	{
	    if (!$ignoreInternal) return self::$allPrimitives;
	     
	    $ret = array();
	    foreach (self::$allPrimitives as $prm) {
	        if (!$prm->internal) $ret[] = $prm;
	    }
	    return $ret;
	}
	
	// ############################################
    // Private
    // ############################################
    
	
	private function parsePrimitive($id, $obj)
	{
	    $ret = new Mira_Core_Primitive();
	    $ret->name = $obj["name"];
	    $ret->sqlType = $obj["sql"];
	    $ret->id = $id; 
	    $ret->internal = isset($obj["priv"]) ? $obj["priv"] : false;
	    return $ret;
	}
}
