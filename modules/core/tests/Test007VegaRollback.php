<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';
require_once 'Test002VegaTypeCreation.php';

class Test007VegaRollback extends Mira_Core_Test_TestCase
{
	
	public static function setUpBeforeClass()
    {
        self::$sqldump = dirname(__FILE__) . '/../../../resources/tests/dump.sql';
        self::$config = dirname(__FILE__) . '/../../../resources/tests/config.ini';
        parent::setUpBeforeClass();
    }
    
	public $email = "ayn_nian@hotmail.com";
	public $pass = "andres";
    
    /**
     * @depends Test002VegaTypeCreation::testRestore
     * 
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testLastRevisionVega()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
		
		//1. creating the FIRST VEGA
		$vegaType = $api->tname("Developper");
		$vega = $api->createVega($vegaType, "Andrea Developper", $user);
		$firstName = "first name";
		$vega->$firstName = "Andrea";
		$lastName = "last name";
		$vega->$lastName = "Franco";
		$vega->age = 24;
		$yoe = "years of experience";
		$vega->$yoe = 0;
		$vega->working = 0;
		$vega->boss =  $api->vid(1);
		$vega->save();
		
		//testing the Vega
		$vega = $api->vid($vega->id);
		$this->assertSame("Andrea",$vega->$firstName);
		$this->assertSame("Franco",$vega->$lastName);
		$this->assertEquals(24,$vega->age);
		$this->assertEquals(0,$vega->$yoe);
		$this->assertEquals(0,$vega->working);
		$this->assertSame("Andres PENA",$vega->boss->name);
		$this->assertSame("ayn_nian@hotmail.com",$vega->boss->email);

		//Editing the vega
		$vega->name = "Andrea Johanna Developper";
		$vega->boss = $api->vname("Mathieu Lemaire");
		$vega->save();
		
		//testing
		$vega = $api->vid($vega->id);
		$this->assertSame("Andrea Johanna Developper",$vega->name);
		$this->assertSame("Mathieu Lemaire",$vega->boss->name);
		
		//get the first revision
		$vega->rollbackToRevision($vega->rv_vg - 1);
		$this->assertSame("Andres PENA",$vega->boss->name);
		$this->assertSame("ayn_nian@hotmail.com",$vega->boss->email);
	}
    
   
	
}
