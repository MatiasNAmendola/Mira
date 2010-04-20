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
 * @see Zend_Gdata
 */
require_once 'Zend/Gdata.php';

/**
 * @see Mira_Gdata_Contacts_ListFeed
 */
require_once 'Mira/Gdata/Contacts/ListFeed.php';

/**
 * @see Mira_Gdata_Contacts_ListEntry
 */
require_once 'Mira/Gdata/Contacts/ListEntry.php';


/**
 * Service class for interacting with the Google Contacts data API
 * @link http://code.google.com/apis/contacts/
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Mira_Gdata_Contacts extends Zend_Gdata
{

    const CONTACTS_FEED_URI = 'http://www.google.com/m8/feeds/contacts/default';
    const AUTH_SERVICE_NAME = 'cp';

    protected $_defaultPostUri = self::CONTACTS_FEED_URI;

    /**
     * The projection determines how much detail should be given in the
     * result of the feed.
     *
     * @link 	http://code.google.com/apis/contacts/reference.html#Projections
     * @var string
     */
    protected $_projection = 'full';

    public static $namespaces = array(
            'gd' => 'http://schemas.google.com/g/2005');

    /**
     * Create Gdata_Contacts object
     *
     * @param Zend_Http_Client $client (optional) The HTTP client to use when
     *          when communicating with the Google servers.
     * @param string $applicationId The identity of the app in the form of Company-AppName-Version
     */
    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Mira_Gdata_Contacts');
        //$this->registerPackage('Mira_Gdata_Contacts_Extension');
        parent::__construct($client, $applicationId);
        $this->_httpClient->setParameterPost('service', self::AUTH_SERVICE_NAME);
    }

    /**
     * Creates and returns a new query object.
     *
     * The returned object will inherit it's initial projection value.
     *
     * @return Mira_Gdata_Contacts_Query
     */
    public function newContactQuery(){
        $q = new Mira_Gdata_Contacts_Query();
        $q->setProjection($this->getProjection());
         
        return($q);
         
    }

    /**
     * Sets the projection for this feed. Valid values for Contacts are "full",
     * "thin", and "property-KEY" where KEY is an extended property name.
     *
     * Projections mainly influence the visibility of extended properties.
     *
     * The default is "full".
     *
     * @param string $value
     * @return Mira_Gdata_Contacts Provides a fluent interface
     */
    public function setProjection($value)
    {
        $this->_projection = $value;
        return $this;
    }


    /**
     * Gets the projection for this feed.
     *
     * @return string projection
     */
    public function getProjection()
    {
        return $this->_projection;
    }

    /**
     * Retrieve feed object
     *
     * @return Mira_Gdata_Contacts_ListFeed
     */
    public function getContactListFeed($current = null, $nb = null)
    {
        $uri = self::CONTACTS_FEED_URI;
        if($this->_projection){
            $uri .= '/' . $this->_projection;
        }
		$location = new Zend_Gdata_Query($uri);
		if ($nb != null) $location->setMaxResults($nb);
		if ($current != null) $location->setStartIndex($current);
        return parent::getFeed($location,'Mira_Gdata_Contacts_ListFeed');
    }
	
    
    public function getAllContacts() {
    	$uri = self::CONTACTS_FEED_URI;
        if($this->_projection){
            $uri .= '/' . $this->_projection;
        }
        return parent::retrieveAllEntriesForFeed(parent::getFeed($uri,'Mira_Gdata_Contacts_ListFeed'));
    }
    
	public function retrieveAllEntriesForFeedCurrent($feed, $current, $nb) {
        $feedClass = get_class($feed);
        $reflectionObj = new ReflectionClass($feedClass);
        $result = $reflectionObj->newInstance();
        do {
            foreach ($feed as $entry) {
                $result->addEntry($entry);
            }

            $next = $feed->getLink('next');
            if ($next !== null) {
                $feed = $this->getFeed($next->href, $feedClass);
            } else {
                $feed = null;
            }
        }
        while ($feed != null);
        return $result;
    }

}