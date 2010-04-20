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
 * @package    Mira_File
 * @subpackage Zend
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @category   Mira
 * @package    Mira_File
 * @subpackage Zend
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_File_Zend_Controller extends Mira_Core_Zend_AbstractController
{
    // @var Mira_File_Server
    protected $server; 
    
    public function init ()
    {
        $this->server = Mira_File_Server::getInstance();
    }
    
    /**
     * @todo Not tested yet
     */
    public function addAction ()
    {
    	$fileName =    $this->_request->getPost("filename");
    	$description = $this->_request->getPost("description");
    	$author =      $this->_request->getPost("author");
    	$url =         $this->_request->getPost("url");
    	
    	$file = null;
    	
    	if (!Mira_Utils_String::isEmpty($url)) {
    	    // download a file from a URL location
    	    $file = $this->server->addFromUrl($url, $fileName, $description, $author);
    	} else if (isset($_FILES['file']['tmp_name'])) {    	    
    	    // upload a file via HTTP
    	    $file = $this->server->addFromHttpRequest($fileName, $description, $author);
    	}
        $this->view->file = $file;
    }
}