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
 * Zend_Gdata_Query
 */
require_once('Zend/Gdata/Query.php');

/**
 * Assists in constructing queries for Google Document List documents
 *
 * @link http://code.google.com/apis/gdata/spreadsheets/
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Mira_Gdata_Contacts_Query extends Zend_Gdata_Query
{
        
	const ORDER_LASTMODIFIED = "lastmodified";
		
    /**
     * The generic base URL used by some inherited methods
     *
     * @var string
     */
    protected $_defaultFeedUri = Mira_Gdata_Contacts::CONTACTS_FEED_URI;   

    /**
     * The projection determines how much detail should be given in the
     * result of the query. 
     * 
     * @link 	http://code.google.com/apis/contacts/reference.html#Projections
     * @var string
     */
    protected $_projection = 'full';
   
    /**
     * The ordering to use when retrieving results.
     * 
     * This will generally correspond to one of the self::ORDER_* constants 
     *
     * @var string
     */
    protected $_ordering = self::ORDER_LASTMODIFIED;
    
    /**
     * Whether to show contacts marked "deleted" or not. Defaults to false.
     *
     * @var boolean
     */
    protected $_showDeleted = false;
    
    /**
     * Whether to order results in ascending or descending order. True for
     * ascending, false for descending. Defaults to true.
     *
     * @var boolean
     */
    protected $_ascending = true;
    
    /**
     * If present, a string ID for the proup to restrict results to.
     * Defaults to a blank string for no group restrictions.
     *
     * @var string
     */
    protected $_onlyGroup = '';
    /**
     * Constructs a new instance of a Mira_Gdata_Contacts_Query object.
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Sets the ordering for results. Some supported ordering schemes may be
     * found using the self::ORDER_* constants.
     *
     * @param string $type Ordering scheme label
     * @return boolean True if successfully set, false otherwise
     */
    public function setOrdering($type){
    	if(strlen($type) <= 0){
    		return(false);
    	}
    	$this->_ordering = $type;
    	
		if($this->_ordering){
    		$this->_params['orderby'] = $this->_ordering;
    	}else{
    		unset($this->_params['orderby']);
    	}
    	return(true);
    }
    
    /**
     * Retrieves the ordering used when making queries. 
     *
     * @return string
     */
    public function getOrdering(){
    	return($this->_ordering);
    }
    
    /**
     * Sets sort order. True for ascending, false for descending.
     *
     * @param boolean $asc
     */
    public function setAscending($asc){
    	$this->_ascending = (boolean)$asc;
    	
    	if($this->_ascending){
    		$this->_params['sortorder'] = 'ascending';
    	}else{
    		$this->_params['sortorder'] = 'descending';
    	}
    }
    /**
     * Retrieves sort order. True for ascending, false for descending.
     *
     * @return boolean
     */
    public function isAscending(){
    	return($this->_ascending);
    }
    
    /**
     * Sets whether "deleted" contacts are returned in results or not.
     *
     * @param boolean $show
     */
    public function setShowingDeleted($show){
    	$this->_showDeleted = (boolean)$show;
    	
        if($this->_showDeleted){
    		$this->_params['showdeleted'] = 'true';
    	}else{
    		$this->_params['showdeleted'] = 'false	';
    	}
    }
    /**
     * Retrives whether or not "deleted" contacts will appear in results.
     *
     * @return boolean
     */
    public function isShowingDeleted(){
    	return($this->_showDeleted);
    }

    /**
     * Sets the group ID that a contact must exist within in order to be in the
     * query result. This group ID will typically be an URI.
     * 
     * Any setting other than a positive-length string will disable this 
     * result restriction.
     * 
     * @link http://code.google.com/apis/contacts/reference.html#GroupElements
     * @param string $uri
     */
    public function setGroup($uri){
    	if(strlen($uri) <= 0){
    		return(false);
    	}
    	$this->_onlyGroup = $uri;
    	
		if($this->_onlyGroup){
    		$this->_params['group'] = $this->_onlyGroup;
    	}else{
    		unset($this->_params['group']);
    	}
    	return(true);
    }    
    /**
     * Sets the projection for this query. Valid values for Contacts are "full", 
     * "thin", and "property-KEY" where KEY is an extended property name. 
     * 
     * Projections mainly influence the visibility of extended properties.
     *
     * @param string $value
     * @return Mira_Gdata_Contacts_Query Provides a fluent interface
     */
    public function setProjection($value)
    {
        $this->_projection = $value;
        return $this;
    }


    /**
     * Gets the projection for this query.
     *
     * @return string projection
     */
    public function getProjection()
    {
        return $this->_projection;
    }
    

    /**
     * Gets the full query URL for this query.
     *
     * @return string url
     */
    public function getQueryUrl()
    {
        $uri = $this->_defaultFeedUri;
        
        if ($this->_projection !== null) {
            $uri .= '/' . $this->_projection;
        } else {
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception(
                'A projection must be provided for contact queries.'); //TODO is this true? Test.
        }

        $uri .= $this->getQueryString();
        return $uri;
    }

}
