<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

class Test002VegaTypeCreation extends Mira_Core_Test_TestCase
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
		//creating the vegaType
		
        //login
        $api->login($this->email, $this->pass);
        $user = $api->getUser();

        // create and save the actual vegatype
        $vegaType = $api->createVegaType("Project", $user);
        
        $nameProp = $vegaType->createProperty();
        $nameProp->name = "NameProj";
        $nameProp->position = 1;
        $nameProp->type = 1;
        
        $manyProp = $vegaType->createProperty();
        $manyProp->name = "Number of Person";
        $manyProp->position = 2;
        $manyProp->type = 1;
        
        $dateBeganProp = $vegaType->createProperty();
        $dateBeganProp->name = "date began";
        $dateBeganProp->position = 3;
        $dateBeganProp->type = 3;
        
        $countryProp = $vegaType->createProperty();
        $countryProp->name = "Country of Project";
        $countryProp->position = 4;
        $countryProp->type = 1;
        
        $vegaTypeProp = $vegaType->createProperty();
        $vegaTypeProp->name = "Director of Project";
        $vegaTypeProp->position = 5;
        $vegaTypeProp->type = $api->tname("Contact");
        
		$vegaType->save();
		
		$vegaType = $api->tid($vegaType->id);
		//testing the vegaType
		$this->assertSame($vegaType->name, "Project");
		$this->assertSame($vegaType->owner->id, $user->id);
		$this->assertEquals(count($vegaType->getVegaProperties()), 5);
		$this->assertEquals($vegaType->propertyWithName("Number of Person")->name, "Number of Person");
		$this->assertEquals($vegaType->propertyWithName("date began")->name, "date began");
		$this->assertEquals($vegaType->propertyWithName("Country of Project")->name, "Country of Project");
		$this->assertEquals($vegaType->propertyWithName("Country of Project")->type, 1);
		$this->assertEquals($vegaType->propertyWithName("Director of Project")->name, "Director of Project");
		$this->assertSame($vegaType->fqn,"User_" . $user->id . "_" . $vegaType->name);
	}
    
    /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testEditVegaType()
	{
	    $api = new Mira("application");
		//find VegaType by Name
		$vegaType = $api->tname("Project");
		$this->assertSame($vegaType->name, "Project");
		
		//find VegaProperties by Name
		$prpToDelete = $vegaType->propertyWithName("Country of Project");
		$prpToEdit = $vegaType->propertyWithName("NameProj");
		
		//removing the property
		$vegaType->removePropertyWithId($prpToDelete->id);
		$this->assertEquals(count($vegaType->getVegaProperties()),4);
		
		//editing the property
		$prpToEdit->name = "Name of the Project";
		
		$prpToEdit = $vegaType->propertyWithName("Name of the Project");
		$this->assertSame("Name of the Project",$prpToEdit->name);
		
		//editing the vegatype Name
		$vegaType->name = "My Projects";
		$vegaType->save();
		
		//find VegaType by Name
		$vegaType = $api->tname("My Projects");
		$this->assertSame($vegaType->name,"My Projects");
		
		//creating the new property to add
		$prpToAdd = $vegaType->createProperty();
		$prpToAdd->name = "state";
		$prpToAdd->position = 4;
		$prpToAdd->type = 1; 
		
		$vegaType->save();
		
		//adding the new property
		$this->assertEquals(count($vegaType->getVegaProperties()),5);	
	}
	
    /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testDeleteVegaType()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
	    
		//creating the VegaType to delete after
	    $vegaType = $api->createVegaType("Developper", $user);
	    
		$first_nameProp = $vegaType->createProperty();
		$first_nameProp->name = "first name";
		$first_nameProp->position = 1;
		$first_nameProp->type = 1; 
		
		$last_nameProp = $vegaType->createProperty();
		$last_nameProp->name = "last name";
		$last_nameProp->position = 2;
		$last_nameProp->type = 1;
		 
		$ageProp = $vegaType->createProperty();
		$ageProp->name = "age";
		$ageProp->position = 3;
		$ageProp->type = 2;
		 
		$yoeProp = $vegaType->createProperty();
		$yoeProp->name = "years of experience";
		$yoeProp->position = 4;
		$yoeProp->type = 2; 
		
		$actProp = $vegaType->createProperty();
		$actProp->name = "working";
		$actProp->position = 5;
		$actProp->type = 5;
		 
		$vegaTypeProp = $vegaType->createProperty();
		$vegaTypeProp->name = "boss";
		$vegaTypeProp->position = 6;
		$vegaTypeProp->type = $api->tname("Contact"); 
                
		$vegaType->save();

		//testing the VegaType
		$this->assertSame($vegaType->name,"Developper");
		$this->assertSame($vegaType->owner->id,$user->id);
		$this->assertEquals(count($vegaType->getVegaProperties()),6);
		$this->assertEquals($vegaType->propertyWithName("first name")->name,"first name");
		$this->assertEquals($vegaType->propertyWithName("last name")->name,"last name");
		$this->assertEquals($vegaType->propertyWithName("age")->name,"age");
		$this->assertEquals($vegaType->propertyWithName("years of experience")->name,"years of experience");
		$this->assertEquals($vegaType->propertyWithName("working")->name,"working");
		$this->assertEquals($vegaType->propertyWithName("boss")->name,"boss");
		
		//vegatype deleted
		$vegaType->delete();
		$vegaType = $api->tname("Developper");

		//testing it is deleted
		$this->assertSame($vegaType, null, "VegaType Developper has not been correctly deleted");	
	}
	
	/**
	 * @depends testDeleteVegaType
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
	 */
	public function testRestore()
	{
	   $api = new Mira("application");
	   $vegaType = $api->selectVegaTypes()
	               ->where("name", "Developper")
	               ->where("status", "trashed")
	               ->fetchObject();
	               
	   $this->assertSame($vegaType->name,"Developper");	
	     
	   $vegaType->restore(); 
	   $vegaType = $api->tname("Developper");
	   $this->assertSame($vegaType->name,"Developper");
	    
	}
	
	/**
	 * It won't output any exception, but change the name of
	 * the type being restored
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
	 */
	public function testRestoreOfSameName()
	{
	    // login 
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
        // first one passes
        $vegaType = $api->createVegaType("RestoreTypeSameName", $api->getUser());
	    $vegaType->save();
	    $vegaType->delete();
	    $id = $vegaType->id;
	    
        $vegaType2 = $api->createVegaType("RestoreTypeSameName", $api->getUser());
	    $vegaType2->save();
	    
	    $vegaType3 = $api->selectVegaTypes()
	                 ->where("id", $id)
	                 ->where("status", "trashed")
	                 ->fetchObject();
	    $vegaType3->restore();
	    $this->assertSame($vegaType3->name,"RestoreTypeSameName 1");	    
	}
}
