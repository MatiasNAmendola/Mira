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
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @category   Mira
 * @package    Mira_File
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_File_Application extends Mira_Core_Application_Abstract 
{
    const REG_FILESPATH = "filesPath";
    
    public static $_instance = null;
    
    // @var Mira_Core_VegaType
    protected $vegaType = null;
    // @var Mira
    protected $api;
    
    // @var string
    private $_filePath;
    // @var string
    private $_baseUrl;

    public function __construct($filesPath = null, $baseUrl = null)
    {
        $this->api = Zend_Registry::get(Mira_Core_Constants::REG_API);
	    $this->vegaType = $this->api->tfqn("Mira_File");
	    
        $root = Zend_Registry::get(Mira_Core_Constants::REG_ROOT);
        
        $conf = Zend_Registry::get(Mira_Core_Constants::REG_CONFIG);
	    if (Mira_Utils_String::isEmpty($baseUrl)) {
            $base = $conf->base->toArray();
            $baseUrl = $base["url"];	        
	    }
	    if (Mira_Utils_String::isEmpty($filesPath) || !is_dir($filesPath)) {
            $files = $conf->files->toArray();
	        $filesPath = $files["path"]; 
	    }
	    $this->_filesPath = $root . $filesPath;
	    $this->_baseUrl = $baseUrl;
    }

    // ############################################
    // Application implementation
    // ############################################
    
	public function install()
	{
	    if ($this->isInstalled()) return;
	    
        // File, fqn = Mira_File
	    $vegaType = $this->api->createVegaType("File", 0);
		$vegaType->fqn = "Mira_File";
		// create some basic properties
		$urlProp = $vegaType->createProperty("url", 6);
		$authorProp = $vegaType->createProperty("author", 1);
		$fileTypeProp = $vegaType->createProperty("file type", 1);
		$sizeProp = $vegaType->createProperty("size", 2);
		$descriptionProp = $vegaType->createProperty("description", 1);
        // save
		$vegaType->save();
		$this->vegaType = $vegaType;
	}
	
	public function isInstalled()
	{
	    return ($this->vegaType !== null);
	}
	
	public function uninstall() {} // @todo
	public function start() {}
	public function stop() {} 
     
    /**
     * Creates a Vega File
     * 
     * This method should not be called directly. It is used exclusively by FileController
     * which controls the upload, naming and save of the file (from a form POST, or a given URL).
     * 
     * @see FileController
     * 
     * @param string $url
     * @param string $path
     * @param string $fileName
     * @param string $fileExtension 
     * @param Mira_Core_User | integer $userId
     * @return Mira_File unsaved, make sure to call save() on this object.
     */
    public function create($url, $fileName = "file", $fileExtension = "", $fileSize = 0,
                $description = "", $author = "")
    {    
        $file = $this->api->createVega($this->vegaType, $fileName, $this->api->getUser());
        $fileType = "file type";
   		$file->url = $url;
   		$file->$fileType = $fileExtension == "" ? "" : substr($fileExtension, 1);
    	return $file;
    }
    
    /**
     * @param string $filename
     * @return string 
     */
    public function buildUniqueFilename($filename)
    {    	
        $hex = dechex(rand(0, 65535));
        while(file_exists ($this->_filesPath . $hex . "_" . $filename)){
        	$hex = dechex(rand(0, 65535));
        }
        return $hex . "_" . $filename;
    }
    
    public function addFromUrl($url, $fileName = "", $description = "", $author = "", $download = false)
    {
        $fileExtension = null;
        $fileSize = 0;
        $actualFileName = strrchr($url, '/');
        if(strrchr($url, '.')){    	    	
            $fileExtension = strrchr($actualFileName, '.');
        }
        if (Mira_Utils_String::isEmpty($fileName)) {
            $fileName = $actualFileName;
        }
        
        if ($download) {
            $newName = $this->buildUniqueFilename($fileName.$fileExtension);
    		copy($url, $this->_filePath . $newName);
    		$url = $this->_baseUrl . "/files/$newName";
    		$fileSize = filesize($this->_filePath . $newName);
        } else {
            $fileSize = $this->getRemoteFileSize($url, $fileExtension);
        }
        
        $file = $this->create($url, $fileName, $fileExtension, $fileSize, $description, $author);
    }
    
    public function addFromHttpRequest($fileName = "", $description = "", $author = "")
    {
        $actualFileName = $_FILES['file']['name'];
	    $fileExtension = strrchr($actualFileName, '.');   
        if(strrchr($actualFileName, '.')){    	    	
            $fileExtension = strrchr($actualFileName, '.');
        }
        if (Mira_Utils_String::isEmpty($fileName)) {
            $fileName = $actualFileName;
        }   
         
	    $newName = $this->buildUniqueFilename($fileName.$fileExtension);     
	    move_uploaded_file($_FILES['file']['tmp_name'], $this->_filePath . $newName);
        $url = $this->_baseUrl . "/files/$newName";
		$fileSize = filesize($this->_filePath . $newName);
        
        $file = $this->create($url, $fileName, $fileExtension, $description, $author);
    }
    
    public static function getRemoteFileSize($url)
    {
        if (substr($url,0,4)=='http') { 
            $x = array_change_key_case(get_headers($url, 1),CASE_LOWER); 
            if ( strcasecmp($x[0], 'HTTP/1.1 200 OK') != 0 ) { $x = $x['content-length'][1]; } 
            else { $x = $x['content-length']; } 
        } 
        else { $x = @filesize($url); } 
    
        return $x; 
    }
    
}
