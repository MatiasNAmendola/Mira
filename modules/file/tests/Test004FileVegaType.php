<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

class Test004FileVegaType extends Mira_Core_Test_TestCase
{
	
	public static function setUpBeforeClass()
    {
        self::$config = dirname(__FILE__) . '/../../../resources/tests/config.ini';
        parent::setUpBeforeClass();
    }
    
	public $email = "ayn_nian@hotmail.com";
	public $pass = "andres";
	
    /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testCreateVegaFileWithExtension()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
	    
        $root = Zend_Registry::get(Mira_Core_Constants::REG_ROOT);
        $pathFile = Zend_Registry::get(Mira_Core_Constants::REG_FILESPATH);
        $fileType = "file type";
		
		// creating a vega file with an extension
		// this is to replace the work done by the controller
		// itself that retrieves file from POST variable and put
		// it in public/files folder
		$myFile = fopen($root . $pathFile . "mifile.txt","w+");     
        $path = $root . $pathFile . "mifile.txt";   
        fputs ($myFile, "Creating a File"); 
        fclose($myFile);
        $targetUrl = BASE_URL."/files/mifile.txt";
		$fileName = "my document";
        $file = $api->vegaTypeFile()->create($targetUrl, $path, $fileName, ".txt", $user->id);
        $file->description = "my first file";
        $file->author = "Me";
        $file->save();
        
        $this->assertSame("txt",$file->$fileType);
        $this->assertSame($file->type->fqn,"Mira_Core_" . $file->type->name);
	}
	
    
    /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testCreateVegaFileWithoutExtension()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
	    
        $root = Zend_Registry::get(Mira_Core_Constants::REG_ROOT);
        $pathFile = Zend_Registry::get(Mira_Core_Constants::REG_FILESPATH);
        $fileType = "file type";
		
        //creating a vega file without an exception
        $myFile = fopen($root . $pathFile . "withoutextension","w+");     
        $path = $root . $pathFile . "withoutextension";   
        $a = fputs ($myFile, "Creating a Second File without an extension"); 
        $b = fclose($myFile);
        $targetUrl = BASE_URL."/files/withoutextension";
		$fileName = "other document";
        $file = $api->vegaTypeFile()->create($targetUrl, $path, $fileName, "", $user->id);
        $file->description = "my second file";
        $file->author = "Me";
        $file->save();
        
        $this->assertSame("",$file->$fileType);
	}
    
    /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testCreateVegaFileController()
	{	
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
	    
        $this->request->setMethod("POST");
        $this->request->setPost(array("filename" => "photo",
                                      "description"=>"my first photo",
                                      "author"=>"internet",
                                      "url"=>"http://ilt.typography.netdna-cdn.com/img/alec/a.jpg"));
        $this->dispatch('/file/add');          
	}   
}