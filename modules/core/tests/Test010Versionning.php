<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

class Test010Versionning extends Mira_Core_Test_TestCase
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
     * @codereview_reviewer karla
     * @codereview_status accepted
     */
    public function testIsBrandNew()
    {
        $api = new Mira("application");
        $api->login($this->email,$this->pass);
        $user = $api->getUser();
    	$vegaType = $api->tname("Contact");
    	$vega = $api->createVega($vegaType, "Iron Maiden", $user);
    	
    	$firstName = "first name";
    	$lastName = "last name";
    	$vega->$firstName = "Iron";
    	$vega->$lastName = "Maiden";
    	$vega->email = "ironmaiden@hotmail.com";
    	
    	//it will be true because is new
    	$this->assertTrue($vega->isBrandNew());
    	
    	$vega->save();
    	
    	//it will be false because is not new
    	$this->assertFalse($vega->isBrandNew());
    }
    
    /**
     * @codereview_owner andres
     * @codereview_reviewer karla
     * @codereview_status accepted
     */
    public function testIsDirty()
    {
        $api = new Mira("application");
    	$vega = $api->vname("Iron Maiden");
    	//the vega is not dirty yet
    	$this->assertFalse($vega->isDirty());
    	
    	$vega->name = "Iron Maiden 2";
    	//it is dirty now
    	$this->assertTrue($vega->isDirty());
    }
    
    /**
     * @codereview_owner andres
     * @codereview_reviewer karla
     * @codereview_status accepted
     */
    public function testHasPrevHasNext()
    {
        $api = new Mira("application");
    	$vega = $api->vname("Iron Maiden");
    	//the vega doesn't have next revision or previus revision
    	$this->assertFalse($vega->hasNext());
    	$this->assertFalse($vega->hasPrevious());
    	
    	$vega->name = "Iron Maiden 2";
    	$vega->save();
    	
    	$this->assertTrue($vega->hasPrevious());
    	$vega->moveToRevision(1);
    	$this->assertTrue($vega->hasNext());
    }
    
}
