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
 * @subpackage Application
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * This defines a logic of "applications" that can be started from Mira. The application
 * is very close to the notion of "Server" - you can {@link install()} it, {@link start()} it... etc. 
 * 
 * For instance, we are implementing a full text search index that will watch for Vega changes. This will be 
 * started using this application logic.
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Application
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
abstract class Mira_Core_Application_Abstract
{
	/**
	 * @var Mira
	 */
	protected $_api;
	
	public function __construct() {
		$this->_api = Zend_Registry::get(Mira_Core_Constants::REG_API);
	}
	
	/**
	 * This is called by Mira at launch. This funciton can be used
	 * to place any first-time-run logic (create some Vega Types, create
	 * some folders... etc)
	 */
	abstract function install();
	
	/**
	 * clean after run
	 */
	abstract function uninstall();
	
	abstract function start();
	
	abstract function stop();

}