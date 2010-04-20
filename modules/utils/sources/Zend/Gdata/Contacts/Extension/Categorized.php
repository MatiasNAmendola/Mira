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
 * @see Mira_Gdata_Contacts_Extension_Categorizable
 */
require_once 'Mira/Gdata/Contacts/Extension/Categorizable.php';

/**
 * Represents shared functionality for several elements used by the Contacts data API
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Mira_Gdata_Contacts_Extension_Categorized extends Zend_Gdata_Extension implements Mira_Gdata_Contacts_Extension_Categorizable
{

    /**
     * On reading, check 'rel' attribute. If not present, check 'label' attribute.
     * On writing, use 'rel' only if the category is one of the predefined ones.
     */
    const BEHAVIOR_NORM 		= 0;


    protected static $categories = array('work','personal','other');
    protected $_rootNamespace = null;
    protected $_rootElement = null;

    protected $_value_rel = null;
    protected $_value_label = null;

    /**
     * The behavior flag here is for flexibility if/when the means for encoding
     * category membership changes or bugs come up.
     *
     * @var integer A self::BEHAVIOR_* class constant value
     */
    protected $_categoryBehavior = 0; // Subclasses should override as appropriate

    /**
     * Constructs a new object. (This class is abstract.)
     * @param string $value (optional) The text content of the element.
     */
    public function __construct($value = null)
    {
        foreach (Mira_Gdata_Contacts::$namespaces as $nsPrefix => $nsUri) {
            $this->registerNamespace($nsPrefix, $nsUri);
        }
        parent::__construct();
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
        // We assume that the values have been correctly set already
        self::setDomAttribute($element,'rel',$this->_value_rel);
        self::setDomAttribute($element,'label',$this->_value_label);
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
            case 'rel': // fall-through
                $this->_value_rel = $attribute->nodeValue;
                break;
            case 'label':
                $this->_value_label = $attribute->nodeValue;
                break;
            default:
                parent::takeAttributeFromDOM($attribute);
        }
    }

    protected function determineCategory(){
        switch($this->_categoryBehavior)
        {
            case self::BEHAVIOR_NORM:
                $val = false;
                // Check rel's value
                if($this->_value_rel){
                    $bits = explode("#",$this->_value_rel);
                    if(sizeof($bits)==2){
                        assert($bits[0] == $this->lookupNamespace($this->_rootNamespace));
                        $val = $bits[1];
                        assert(in_array($val,self::$categories));
                        return($val);
                    }
                }
                /**
                 * Okay, so the rel attribute wasn't any help. Try label.
                 * Note that some items use additional data in the label.
                 * For example, for phone numbers, the label might be:
                 * 		"Category / Mobile"
                 *
                 * This class does NOT deal with the additional data or the
                 * slash separator. That's a matter for subclasses to
                 * handle.
                 */
                	
                if($this->_value_label){
                    return($this->_value_label);
                }
                	
                // Okay, so nothing (yet?)
                return(FALSE);
                break;
        }
    }


    /**
     * Get the value for this element's value attribute.
     *
     * @return string The category associated with this e-mail or null if not present
     */
    public function getCategory()
    {
        return($this->determineCategory());
    }

    /**
     * Sets the category for this element
     *
     * @param string $value The desired category (work, personal, other)
     * @return Mira_Gdata_Contacts_Extension_Email The element being modified.
     */
    public function setCategory($value)
    {
        if(in_array($value,self::$categories)){
            $this->_value_rel = $this->lookupNamespace($this->_rootNamespace) . "#" . $value;
            $this->_value_label = false;
        }else{
            $this->_value_rel = false;;
            $this->_value_label = $value;
        }
        return $this;
    }

}
