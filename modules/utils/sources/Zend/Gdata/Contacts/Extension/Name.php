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
 * Represents the title element used by the Contacts data API to define the name
 * of a contact.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Mira_Gdata_Contacts_Extension_Name extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'atom';
    protected $_rootElement = 'title';

    /**
     * Constructs a new Mira_Gdata_Contacts_Extension_Name object.
     * @param string $value (optional) The text content of the element.
     */
    public function __construct($value = null)
    {
        foreach (Mira_Gdata_Contacts::$namespaces as $nsPrefix => $nsUri) {
            $this->registerNamespace($nsPrefix, $nsUri);
        }
        parent::__construct();
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
        if($this->getText() != null){
            return($this->getText());
        }else{
            return('');
        }
    }

}
