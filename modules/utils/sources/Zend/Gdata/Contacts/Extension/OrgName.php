<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Extension
 */
require_once 'Zend/Gdata/Extension.php';

/**
 * Represents the gd:orgName element used by the Contacts data API
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Mira_Gdata_Contacts_Extension_OrgName extends Zend_Gdata_Extension
{
    protected $_rootNamespace = 'gd';
    protected $_rootElement = 'orgName';

    /**
     * Constructs a new Mira_Gdata_Contacts_Extension_Organization object.
     * @param string $value (optional) The text content of the element.
     */
    public function __construct($value = null)
    {
        parent::__construct($value);
        if($value !== null){
            $this->setText($value);
        }
    }

    /**
     * Magic toString method allows using this directly via echo
     * Works best in PHP >= 4.2.0
     */
    public function __toString()
    {
        return((string) $this->getText());
    }


}
