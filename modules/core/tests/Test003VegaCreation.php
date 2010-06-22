<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';
require_once 'Test002VegaTypeCreation.php';

class Test003VegaCreation extends Mira_Core_Test_TestCase
{
	
	public static function setUpBeforeClass()
    {
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
	public function testCreateVega()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    
	    $user = $api->getUser();
		
		//1. creating the FIRST VEGA
		$vegaType = $api->tname("Developper");
		$vega = $api->createVega($vegaType, "first Developper", $user);
		$firstName = "first name";
		$vega->$firstName = "Jhon";
		$lastName = "last name";
		$vega->$lastName = "Doe";
		$vega->age = 24;
		$yoe = "years of experience";
		$vega->$yoe = 0;
		$vega->working = 1;
		$vega->boss =  $api->vname("Andres PENA");
		$vega->save();
		
		//testing the Vega
		$vega = $api->vid($vega->id);
		$this->assertSame("Jhon",$vega->$firstName);
		$this->assertSame("Doe",$vega->$lastName);
		$this->assertEquals(24,$vega->age);
		$this->assertEquals(0,$vega->$yoe);
		$this->assertEquals(1,$vega->working);
		$this->assertSame("Andres PENA",$vega->boss->name);
		$this->assertSame("ayn_nian@hotmail.com",$vega->boss->email);
		
		
		//creating the SECOND VEGA
		$vega = $api->createVega($vegaType->id, "second Developper", $user);
		$firstName = "first name";
		$vega->$firstName = "Peter";
		$lastName = "last name";
		$vega->$lastName = "Petrelli";
		$vega->age = 26;
		$yoe = "years of experience";
		$vega->$yoe = 5;
		$vega->working = 1;
		$vega->boss =  $api->vname("Andres PENA");
		$vega->save();
		
		//testing the Vega
		$vega = $api->vid($vega->id);
		$this->assertSame("Peter",$vega->$firstName);
		$this->assertSame("Petrelli",$vega->$lastName);
		$this->assertEquals(26,$vega->age);
		$this->assertEquals(5,$vega->$yoe);
		$this->assertEquals(1,$vega->working);
		$this->assertSame("Andres PENA",$vega->boss->name);
		$this->assertSame("ayn_nian@hotmail.com",$vega->boss->email);
		$this->assertEquals($vega->type->id,$vegaType->id);
		
		
		//creating the THIRD VEGA
		$vegaType = $api->tname("Contact");
		$vega = $api->createVega($vegaType->id, "Mathieu Lemaire", $user);
		$firstName = "first name";
		$vega->$firstName = "Mathieu";
		$lastName = "last name";
		$vega->$lastName = "Lemaire";
		$vega->email = "lemaire.mathieu@gmail.com";
		$vega->address = "somewhere in france";
		$vega->save();
		
		//testing the Vega
		$vega = $api->vid($vega->id);
		$this->assertSame("Mathieu",$vega->$firstName);
		$this->assertSame("Lemaire",$vega->$lastName);
		$this->assertEquals("lemaire.mathieu@gmail.com",$vega->email);
		$this->assertEquals("somewhere in france",$vega->address);
	}
    
    /**
     * @depends Test002VegaTypeCreation::testRestore
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     * @depends testCreateVega
     */
	public function testEditVega()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    
	    $user = $api->getUser();
		//put the property in a variable to can use them
		$firstName = "first name";
		$lastName = "last name";
		$yoe = "years of experience";
		
		//find the Vega
		$vega = $api->vname("first Developper");
		
		//testing the Vega
		$vega = $api->vid($vega->id);
		$this->assertSame("Jhon",$vega->$firstName);
		$this->assertSame("Doe",$vega->$lastName);
		$this->assertEquals(24,$vega->age);
		$this->assertEquals(0,$vega->$yoe);
		$this->assertEquals(1,$vega->working);
		$this->assertEquals(1,$vega->revision);
		$this->assertSame("Andres PENA",$vega->boss->name);
		
		//Editing the Vega
		$vega->name = "1st Developper";
		$vega->$firstName = "Mauricio";
		$vega->$lastName = "Carmona";
		$vega->$yoe = 1;
		$vega->boss = $api->vname("Mathieu Lemaire");
		$vega->save();
		
		$vegaType = $api->tname("Contact");
		//testing the Vega
		$vega = $api->vid($vega->id);
		$this->assertNotNull($vega);
		$this->assertSame("1st Developper",$vega->name);
		$this->assertSame("Mauricio",$vega->$firstName);
		$this->assertSame("Carmona",$vega->$lastName);
		$this->assertEquals(1,$vega->$yoe);
		$this->assertEquals(2,$vega->revision);
		$this->assertEquals($vega->boss->type->id,$vegaType->id);
		$this->assertSame("Mathieu Lemaire",$vega->boss->name);
	}
	
     /**
     * @depends Test002VegaTypeCreation::testRestore
     * 
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     * @expectedException Exception
     * @depends testEditVega
     */
	public function testSetOtherVegaTypePrp()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    
	    $user = $api->getUser();
		//find the Vega
		$vega = $api->vname("1st Developper");
		//vega->boss is a Contact Type and "second Developper" is a Developper Type so..Exception
		$vega->boss = $api->vname("second Developper");
		$vega->save();
	}
	
    /**
     * @depends Test002VegaTypeCreation::testRestore
     * 
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testDeleteVega()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    
	    $user = $api->getUser();
		//put the property in a variable to can use them
		$firstName = "first name";
		$lastName = "last name";
		$yoe = "years of experience";
		
		//find the Vega
		$vega = $api->vname("1st Developper");
		
		//testing the Vega
		$this->assertSame("1st Developper",$vega->name);
		$this->assertSame("Mauricio",$vega->$firstName);
		$this->assertSame("Carmona",$vega->$lastName);
		$this->assertEquals(1,$vega->$yoe);
		$this->assertEquals(2,$vega->revision);
		
		$vega->delete();
		$vega = $api->vname("1st Developper");
		$this->assertSame(null,$vega);		
	}
	
}
