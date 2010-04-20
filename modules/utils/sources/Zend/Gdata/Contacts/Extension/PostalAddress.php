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
 * @see Mira_Gdata_Contacts_Extension_Categorized
 */
require_once 'Mira/Gdata/Contacts/Extension/Categorized.php';

/**
 * @see Mira_Gdata_Contacts_Extension_Primary
 */
require_once 'Mira/Gdata/Contacts/Extension/Primary.php';

/**
 * Represents the gd:postalAddress element used by the Contacts data API
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Mira_Gdata_Contacts_Extension_PostalAddress extends Mira_Gdata_Contacts_Extension_Categorized
{
    protected $_rootNamespace = 'gd';
    protected $_rootElement = 'postalAddress';
    protected $_isPrimary = false;



    /**
     * Constructs a new Mira_Gdata_Contacts_Extension_PostalAddress object.
     * @param string $value (optional) The text content of the element.
     */
    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->_text = $value;
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for sending to the server upon updates, or
     * for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     * child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc);
        if($this->_isPrimary){
            $element->setAttribute('primary', 'true');
        }else{
            $element->setAttribute('primary', 'false');
        }
        return $element;
    }

    /**
     * Given a DOMNode representing an attribute, tries to map the data into
     * instance members.  If no mapping is defined, the name and value are
     * stored in an array.
     *
     * @param DOMNode $attribute The DOMNode attribute needed to be handled
     */
    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
            case 'primary':
                if(strtolower($attribute->nodeValue) == 'true'){
                    $this->_isPrimary = true;
                }else{
                    $this->_isPrimary = false;
                }
            default:
                parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * Gets the address text for this element.
     *
     */
    public function getAddress()
    {
        return $this->_text;
    }

    /**
     * Sets the address text for this element.
     *
     * @param string $address The phone number to set
     * @return Mira_Gdata_Contacts_Extension_PostalAddress The element being modified.
     */
    public function setAddress($address)
    {
        $this->_text = $address;
        return $this;
    }



    /**
     * Magic toString method allows using this directly via echo
     * Works best in PHP >= 4.2.0
     */
    public function __toString()
    {
        return( (string) $this->getAddress());
    }

    /**
     * Whether or not this object is marked as the 'primary' item of this type for the contact
     *
     * @return boolean True if primary, false otherwise
     */
    public function isPrimary()
    {
        return $this->_isPrimary;
    }

    /**
     * Returns the label used to prevent multiple primaries.
     *
     * All objects with the same label may only have one "primary" item between
     * them, e.g. "email" elements.
     *
     * @return string 	A label unique to a set of items where only one item may
     * 					be set as primary
     */
    public function getPrimaryLabel(){
        return("CONTACT_POSTAL");
    }

    /**
     * Changes the flag for whether this is the primary object among it's peers.
     * Note that this does NOT change the flag on any sibling elements.
     * That task must be managed by caller code.
     *
     * @param boolean $value True to set as primary, false otherwise.
     * @return Mira_Gdata_Contacts_Extension_PostalAddress The element being modified.
     */
    public function setPrimary($value)
    {
        $this->_isPrimary = $value;
        return $this;
    }

}
