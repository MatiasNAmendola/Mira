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
 * @see Zend_Gdata_Entry
 */
require_once 'Zend/Gdata/Entry.php';

/**
 * @see Mira_Gdata_Contacts_Extension_Name
 */
require_once 'Mira/GData/Contacts/Extension/Name.php';

/**
 * @see Mira_Gdata_Contacts_Extension_Notes
 */
require_once 'Mira/GData/Contacts/Extension/Notes.php';

/**
 * @see Mira_Gdata_Contacts_Extension_Email
 */
require_once 'Mira/GData/Contacts/Extension/Email.php';

/**
 * @see Mira_Gdata_Contacts_Extension_Im
 */
require_once 'Mira/GData/Contacts/Extension/Im.php';

/**
 * @see Mira_Gdata_Contacts_Extension_PhoneNumber
 */
require_once 'Mira/GData/Contacts/Extension/PhoneNumber.php';

/**
 * @see Mira_Gdata_Contacts_Extension_PostalAddress
 */
require_once 'Mira/GData/Contacts/Extension/PostalAddress.php';

/**
 * @see Mira_Gdata_Contacts_Extension_Organization
 */
require_once 'Mira/GData/Contacts/Extension/Organization.php';

/**
 * @see Zend_Gdata_Extension_ExtendedProperty
 */
require_once 'Zend/Gdata/Extension/ExtendedProperty.php';

/**
 * Represents a contact entry.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Mira_Gdata_Contacts_ListEntry extends Zend_Gdata_Entry
{

    protected $_contactName 	= null;
    protected $_contactNotes 	= null;
    protected $_emails			= array();
    protected $_ims				= array();
    protected $_phones			= array();
    protected $_postAddr 		= array();
    protected $_orgs			= array();
    protected $_extendedProperty = array();



    public function __construct($element = null)
    {
        foreach (Mira_Gdata_Contacts::$namespaces as $nsPrefix => $nsUri) {
            $this->registerNamespace($nsPrefix, $nsUri);
        }
        parent::__construct($element);
    }

    protected function getAllElements(){
        $allElements = array_merge(
        $this->_emails,
        $this->_ims,
        $this->_phones,
        $this->_postAddr,
        $this->_orgs,
        $this->_extendedProperty
        );
        $allElements[] = $this->_contactNotes;
        $allElements[] = $this->_contactName;
        return($allElements);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc);

        $children = $this->getAllElements();
        self::setDomChildren($element,$children);

        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('atom') . ':' . 'title';
            $item = new Mira_Gdata_Contacts_Extension_Name();
            $item->transferFromDOM($child);
            $this->_contactName = $item;
            break;
            case $this->lookupNamespace('atom') . ':' . 'content';
            $item = new Mira_Gdata_Contacts_Extension_Notes();
            $item->transferFromDOM($child);
            $this->_contactNotes = $item;
            break;
            case $this->lookupNamespace('gd') . ':' . 'email';
            $item = new Mira_Gdata_Contacts_Extension_Email();
            $item->transferFromDOM($child);
            $this->_emails[] = $item;
            break;
            case $this->lookupNamespace('gd') . ':' . 'im';
            $item = new Mira_Gdata_Contacts_Extension_Im();
            $item->transferFromDOM($child);
            $this->_ims[] = $item;
            break;
            case $this->lookupNamespace('gd') . ':' . 'phoneNumber';
            $item = new Mira_Gdata_Contacts_Extension_PhoneNumber();
            $item->transferFromDOM($child);
            $this->_phones[] = $item;
            break;
            case $this->lookupNamespace('gd') . ':' . 'postalAddress';
            $item = new Mira_Gdata_Contacts_Extension_PostalAddress();
            $item->transferFromDOM($child);
            $this->_postAddr[] = $item;
            break;
            case $this->lookupNamespace('gd') . ':' . 'organization';
            $item = new Mira_Gdata_Contacts_Extension_Organization();
            $item->transferFromDOM($child);
            $this->_orgs[] = $item;
            break;
            case $this->lookupNamespace('gd') . ':' . 'extendedProperty';
            $item = new Zend_Gdata_Extension_ExtendedProperty();
            $item->transferFromDOM($child);
            $this->_extendedProperty[] = $item;
            break;

            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    /**
     * Retrieves the name of this contact
     *
     * @return Mira_Gdata_Contacts_Extension_Name 
     */
    public function getName(){
        return($this->_contactName);
    }
    /**
     * @param Mira_Gdata_Contacts_Extension_Name $value
     * @return Zend_Gdata_Extension_ListEntry Provides a fluent interface
     */
    public function setName($value){
        $this->_contactName = $value;
        return($this);
    }
    /**
     * Retrieves the text of any notes associated with this contact.
     *
     * @return Mira_Gdata_Contacts_Extension_Notes Note text
     */
    public function getNotes(){
        return($this->_contactNotes);
    }
    /**
     * @param Mira_Gdata_Contacts_Extension_Notes $value
     * @return Zend_Gdata_Extension_ListEntry Provides a fluent interface
     */
    public function setNotes($value){
        $this->_contactNotes = $value;
        return($this);
    }
    /**
     * Retrieves a list of Mira_Gdata_Contacts_Extension_Email items.
     *
     * @todo return primary first, if any
     * @return array An array of Mira_Gdata_Contacts_Extension_Email objects
     */
    public function getEmails(){
        return($this->_emails);
    }
    /**
     * @param array $values Array of Mira_Gdata_Contacts_Extension_Email items
     * @return Zend_Gdata_Extension_ListEntry or else FALSE on error
     */
    public function setEmails($values){
        foreach($values as $v){
            if(!($v instanceof Mira_Gdata_Contacts_Extension_Email)){
                return(false);
            }
        }
        $this->_emails = $values;
        return($this);
    }
    /**
     * Retrieves a list of Mira_Gdata_Contacts_Extension_Im items.
     *
     * @todo return primary first, if any
     * @return array An array of Mira_Gdata_Contacts_Extension_Im objects
     */
    public function getIms(){
        return($this->_ims);
    }
    /**
     * @param array $values Array of Mira_Gdata_Contacts_Extension_Im items
     * @return Zend_Gdata_Extension_ListEntry or else FALSE on error
     */
    public function setIms($values){
        foreach($values as $v){
            if(!($v instanceof Mira_Gdata_Contacts_Extension_Im)){
                return(false);
            }
        }
        $this->_ims = $values;
        return($this);
    }
    /**
     * Retrieves a list of Mira_Gdata_Contacts_Extension_PhoneNumber items.
     *
     * @todo return primary first, if any
     * @return array An array of Mira_Gdata_Contacts_Extension_PhoneNumber objects
     */
    public function getPhones(){
        return($this->_phones);
    }
    /**
     * @param array $values Array of Mira_Gdata_Contacts_Extension_PhoneNumber items
     * @return Zend_Gdata_Extension_ListEntry or else FALSE on error
     */
    public function setPhones($values){
        foreach($values as $v){
            if(!($v instanceof Mira_Gdata_Contacts_Extension_PhoneNumber)){
                return(false);
            }
        }
        $this->_phones = $values;
        return($this);
    }

    /**
     * Sets the "primary" flag on the given object, and unsets it on all
     * sibling objects.
     *
     * @param Mira_Gdata_Contacts_Extension_Primary $object
     * @return boolean True on success, false on failure.
     */
    public function setPrimary(Mira_Gdata_Contacts_Extension_Primary $object){
        // First, check that the desired primary is already part of our
        // data structure
        $found = false;
        $elements = $this->getAllElements();

        foreach($elements as $e){
            if($e == $object){$found = true;}
        }
        if(!$found){
            return(false);
        }


        $label = $object->getPrimaryLabel();
        // Set false on all items of same type
        foreach($elements as $e){
            if($e instanceof Mira_Gdata_Contacts_Extension_Primary){
                if($label == $e->getPrimaryLabel()){
                    $e->setPrimary(false);
                }
            }
        }
        // Set true on our single chosen primary item
        $object->setPrimary(true);
        return(true);
    }
    /**
     * Retrieves a list of Mira_Gdata_Contacts_Extension_PostalAddress items.
     *
     * @todo return primary first, if any
     * @return array An array of Mira_Gdata_Contacts_Extension_PostalAddress objects
     */
    public function getAddresses(){
        return($this->_postAddr);
    }
    /**
     * @param array $values Array of Mira_Gdata_Contacts_Extension_PostalAddress items
     * @return Zend_Gdata_Extension_ListEntry Provides a fluent interface
     */
    public function setAddresses($values){
        foreach($values as $v){
            if(!($v instanceof Mira_Gdata_Contacts_Extension_PostalAddress)){
                return(false);
            }
        }
        $this->_postAddr = $values;
        return($this);
    }
    /**
     * Retrieves a list of Mira_Gdata_Contacts_Extension_Organization items.
     * 
     * @todo return primary first, if any
     * @return array An array of Mira_Gdata_Contacts_Extension_Organization objects
     */
    public function getOrgs(){
        return($this->_orgs);
    }
    /**
     * @param array $values Array of Mira_Gdata_Contacts_Extension_Organization items
     * @return Zend_Gdata_Extension_ListEntry or else FALSE on error
     */
    public function setOrgs($values){
        foreach($values as $v){
            if(!($v instanceof Mira_Gdata_Contacts_Extension_Organization)){
                return(false);
            }
        }
        $this->_orgs = $values;
        return($this);
    }

    /**
     * Retrieves a list of Zend_Gdata_Extension_ExtendedProperty items.
     *
     * @return array An array of Zend_Gdata_Extension_ExtendedProperty objects
     */
    public function getExtendedProperties()
    {
        return $this->_extendedProperty;
    }

    /**
     * @param array $values Array of Zend_Gdata_Extension_ExtendedProperty items
     * @return Mira_Gdata_Contacts_ListEntry or else FALSE on error
     */
    public function setExtendedProperties($values)
    {
        $keys = array();
        foreach($values as $v){
            if(!($v instanceof Zend_Gdata_Extension_ExtendedProperty)){
                return(false);
            }
            $keys[] = $v->getName();
        }
        $n = sizeof($keys);
        if($n != array_unique($keys)){
            // Dupe key! Not allowed, will prevent saving
            return(false);
        }
        	
        $this->_extendedProperty = $values;
        return $this;
    }
    /**
     * Returns all detected categories for elements
     *
     * @return array Array of string labels
     */
    public function getCategories(){
        $elements = $this->getAllElements();
        $ret = array();
        foreach($elements as $e){
            if($e instanceof Mira_Gdata_Contacts_Extension_Categorizable){
                $ret[] = $e->getCategory();
            }
        }
        return(array_unique($ret));
    }

    /**
     * Returns all categorizable elements of a specific type (e.g. "work", "other", "MyCategory")
     *
     * @param string $name
     * @param string $caseSensitive
     * @return array Array of Zend_Gdata_Extension objects
     */
    public function getByCategory($name,$caseSensitive = true){
        $elements = $this->getAllElements();

        if(!$caseSensitive){
            $name = strtolower($name);
        }
        $ret = array();
        foreach($elements as $e){
            if($e instanceof Mira_Gdata_Contacts_Extension_Categorizable){
                $actual = $e->getCategory();
                if(!$caseSensitive){
                    $actual = strtolower($actual);
                }

                if($name == $actual){
                    $ret[] = $e;
                }
            }
        }
        return($ret);
    }

}
