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
 * @see Mira_Gdata_Contacts_Extension_OrgName
 */
require_once 'Mira/Gdata/Contacts/Extension/OrgName.php';

/**
 * @see Mira_Gdata_Contacts_Extension_OrgTitle
 */
require_once 'Mira/Gdata/Contacts/Extension/OrgTitle.php';

/**
 * @see Mira_Gdata_Contacts_Extension_Primary
 */
require_once 'Mira/Gdata/Contacts/Extension/Primary.php';

/**
 * Represents the gd:organization element used by the Contacts data API
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Mira_Gdata_Contacts_Extension_Organization extends Mira_Gdata_Contacts_Extension_Categorized implements Mira_Gdata_Contacts_Extension_Primary
{
    protected $_rootNamespace = 'gd';
    protected $_rootElement = 'organization';
    protected $_company		= null;
    protected $_jobtitle	= null;
    protected $_isPrimary = false;



    /**
     * Constructs a new Mira_Gdata_Contacts_Extension_Organization object.
     * @param string $value (optional) The text content of the element.
     */
    public function __construct($value = null)
    {
        parent::__construct($value);
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
        self::setDomChild($element,$this->_company);
        self::setDomChild($element,$this->_jobtitle);
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
     * Given a child DOMNode, tries to determine how to map the data into
     * object instance members.  If no mapping is defined, Extension_Element
     * objects are created and stored in an array.
     *
     * @param DOMNode $child The DOMNode needed to be handled
     */
    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gd') . ':' . 'orgName';
            $name = new Mira_Gdata_Contacts_Extension_OrgName();
            $name->transferFromDOM($child);
            $this->_company = $name;
            break;
            break;
            case $this->lookupNamespace('gd') . ':' . 'orgTitle';
            $name = new Mira_Gdata_Contacts_Extension_OrgTitle();
            $name->transferFromDOM($child);
            $this->_jobtitle = $name;
            break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    /**
     * Sets the job title
     *
     * @param string $value Job title
     * @return Mira_Gdata_Contacts_Extension_Organization The element being modified.
     */
    public function setJobTitle($name)
    {
        if($this->_jobtitle == null){
            $this->_jobtitle = new Mira_Gdata_Contacts_Extension_OrgTitle();
        }
        $this->_jobtitle->setText($name);
        return $this;
    }

    /**
     * Retrieves the job title associated with this organizational contact
     *
     * @return string Job title
     */
    public function getJobTitle()
    {
        if($this->_jobtitle != null){
            return $this->_jobtitle->getText();
        }else{
            return(false);
        }
    }

    /**
     * Gets the company name of this organizational contacts
     *
     * @return string Company name
     */
    public function getCompany()
    {
        if($this->_company != null){
            return $this->_company->getText();
        }else{
            return(false);
        }
    }

    /**
     * Sets the company name of this organizational contacts
     *
     * @param string $value Company name
     * @return Mira_Gdata_Contacts_Extension_Organization The element being modified.
     */
    public function setCompany($name)
    {
        if($this->_company == null){
            $this->_company = new Mira_Gdata_Contacts_Extension_OrgName();
        }
        $this->_company->setText($name);
        return $this;
    }



    /**
     * Magic toString method allows using this directly via echo
     * Works best in PHP >= 4.2.0
     */
    public function __toString()
    {
        return( (string) ($this->getCompany()." : ".$this->getJobTitle()) );
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
     * Changes the flag for whether this is the primary object among it's peers.
     * Note that this does NOT change the flag on any sibling elements.
     * That task must be managed by caller code.
     *
     * @param boolean $value True to set as primary, false otherwise.
     * @return Mira_Gdata_Contacts_Extension_Organization The element being modified.
     */
    public function setPrimary($value)
    {
        $this->_isPrimary = $value;
        return $this;
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
        return("CONTACT_ORG");
    }


}
