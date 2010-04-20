<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

class Test008VegaTypeRollback extends Mira_Core_Test_TestCase
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
	public function testCreateVegaType()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
	    
        $contactVegaType = $api->tname("Contact");
	    
        // create and save the actual vegatype
		$vegaType = $api->createVegaType("Disennador", $user);
		
		//creating properties
		$nameProp = $vegaType->createProperty("First Name", 1);
		$softProp = $vegaType->createProperty("Software", 1);
		$workProp = $vegaType->createProperty("Working", 5);
		$vegaTypeProp = $vegaType->createProperty("Recommended", $contactVegaType);
        
		$vegaType->save();
		
		//testing the vegaType
		$this->assertSame($vegaType->name, "Disennador");
		$this->assertSame($vegaType->owner->id, $user->id);
		$this->assertEquals(count($vegaType->getVegaProperties()), 4);
		$this->assertEquals($vegaType->propertyWithName("First Name")->name, "First Name");
		$this->assertEquals($vegaType->propertyWithName("Software")->name, "Software");
		$this->assertEquals($vegaType->propertyWithName("Working")->name, "Working");
		$this->assertEquals($vegaType->propertyWithName("Working")->type, 5);
		$this->assertEquals($vegaType->propertyWithName("Recommended")->name, "Recommended");
		
		// CHANGE VEGATYPE
		$prpToDelete = $vegaType->propertyWithName("Working");
		$prpToEdit = $vegaType->propertyWithName("First Name");
		// removing the property
		$vegaType->removePropertyWithId($prpToDelete->id);
		$this->assertEquals(count($vegaType->getVegaProperties()),3);
		// editing the property
		$prpToEdit->name = "Last Name";
		// editing the vegatype Name
		$vegaType->name = "Designer";
		$vegaType->save();
		// checks
		$vegaType = $api->tname("Designer");
		$this->assertNotNull($vegaType);
		$this->assertSame($vegaType->name, "Designer");
		$this->assertEquals(count($vegaType->getVegaProperties()), 3);
		$this->assertEquals($vegaType->propertyWithName("Last Name")->name, "Last Name");
		$this->assertNull($vegaType->propertyWithName("Working"));
		
		// TEST ROLLBACK
		$vegaType->rollbackToRevision($vegaType->rv_vgt - 1);
		$vegaType = $api->tid($vegaType->id);
		$this->assertNotNull($vegaType);
		// checks
		$this->assertSame($vegaType->name, "Disennador");
		$this->assertSame($vegaType->owner->id, $user->id);
		$this->assertEquals(count($vegaType->getVegaProperties()), 4);
		$this->assertEquals($vegaType->propertyWithName("First Name")->name, "First Name");
		$this->assertEquals($vegaType->propertyWithName("Software")->name, "Software");
		$this->assertEquals($vegaType->propertyWithName("Working")->type, 5);
		$this->assertEquals($vegaType->propertyWithName("Recommended")->name, "Recommended");
	}
   
}
