<?

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
 * @subpackage Zend
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * A Zend Controller that creates a Mira API and sets it as a class member <code>$this->api</code>
 * 
 * @category   Mira
 * @package    Mira_Core
 * @subpackage Zend
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Core_Zend_AbstractController extends Zend_Controller_Action
{
    /**
     * @var Mira
     */
    protected $api;
    
    public function preDispatch()
    {
        $this->api = new Mira();
        $this->api->login();
    }
}