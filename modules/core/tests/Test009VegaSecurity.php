<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

class Test009VegaSecurity extends Mira_Core_Test_TestCase
{
	
	public static function setUpBeforeClass()
    {
        self::$config = dirname(__FILE__) . '/../../../resources/tests/config.ini';
        parent::setUpBeforeClass();
    }
    
	public $email = "ayn_nian@hotmail.com";
	public $pass = "andres";
	public $otherEmail = "andrea@hotmail.com";
	public $otherPass = "andrea";
	
    /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
    public function testCreateUser()
    {
		//creating new user
		$api = new Mira("application");
		$user = $api->createUser($this->otherEmail, $this->otherPass);
		$user->save();
		$user->account = "validated";
		$user->save();
		
		return $user;
    }
    
    /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
    public function testCreateVegaType(){
        //login
        $api = new Mira("application");
        $api->login($this->email, $this->pass);
        
        $user = $api->getUser();
        $contactVegaType = $api->tname("Contact");
        
        // create and save the actual vegatype
		$vegaType = $api->createVegaType("Company", $user);
		
    	//creating the properties
		$nameProp = $vegaType->createProperty("Name", 1);
		$manyProp = $vegaType->createProperty("Number of Person", 1);
		$dateProp = $vegaType->createProperty("date started", 3);
		$countryProp = $vegaType->createProperty("country", 1);
		$vegaTypeProp = $vegaType->createProperty("president", $contactVegaType);

		$vegaType->save();
		
		$prpToAdd = $vegaType->createProperty();
		$prpToAdd->name = "Company Friend";
		$prpToAdd->position = 6;
		$prpToAdd->type = $vegaType;
		$vegaType->save(); 
    }
    /**
     * @depends testCreateUser
     * 
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testCreateVegas($user)
	{
        //login
        $api = new Mira("application");
		//1. creating CONTACT VEGA
		$vegaType = $api->tname("Contact");
		$vega = $api->createVega($vegaType, "Andrea Franco", $user);
		$firstName = "first name";
		$vega->$firstName = "Andrea";
		$lastName = "last name";
		$vega->$lastName = "Franco";
		$vega->email = "andrejohanna@hotmail.com";
		$vega->phone = 2687147;
		$vega->address = "Ibague";
		$vega->save();
		
		$vega = $api->createVega($vegaType, "Mathieu Lemaire", $user);
		$firstName = "first name";
		$vega->$firstName = "Mathieu";
		$lastName = "last name";
		$vega->$lastName = "Lemaire";
		$vega->email = "mathieulemaire@hotmail.com";
		$vega->phone = 318800;
		$vega->address = "Paris";
		$vega->save();
		
		//2. createing COMPANY VEGA
		$vegaType = $api->tname("Company");
		$vega = $api->createVega($vegaType, "Frape Company", $user);
		$vega->Name = "Frape";
		$number = "Number of Person";
		$vega->$number = 2;
		$date = "date started";
		$vega->$date = Zend_Date::now();
		$vega->country = "Colombia";
		$vega->president = $api->vname("Andrea Franco");
		$vega->save();
		
		$vegaType = $api->tname("Company");
		$vega = $api->createVega($vegaType, "Vega Company", $user);
		$vega->Name = "Vega";
		$number = "Number of Person";
		$vega->$number = 2;
		$date = "date started";
		$vega->$date = Zend_Date::now();
		$vega->country = "France";
		$vega->president = $api->vname("Mathieu lemaire");
		$friend = "Company Friend";
		$vega->$friend = $api->vname("Frape Company");
		$vega->save();
	}
    
	/**
	 * @depends testCreateUser
	 * 
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testVegaSecurities($user)
	{
	    $api = new Mira("application");
	    $api->login($this->otherEmail, $this->otherPass);
	    
	    //finding the Vega
	    $selectVega = $api->selectVegas();
	    $selectVega->where("name", "Frape Company");
	    $vegaFrape = $selectVega->fetchObject();
	    
	    $this->assertNotNull($vegaFrape);
	    
	    //change the user
	    $api->logout();
	    $api->login("ayn_nian@hotmail.com", "andres");
	    
	    //searchig a vega without scope
	    $selectVega = $api->selectVegas();
	    $selectVega->where("name", "Frape Company");
	    $vega = $selectVega->fetchObject();
	    
	    //it should be Null
	    $this->assertNull($vega);
	    
	    //adding scope to $vegaFrape
	    $vegaFrape->scope->addUserRole($api->getUser(), "viewer");
        $vegaFrape->save();
        
        //searchig a vega with scope NOW
	    $selectVega = $api->selectVegas();
	    $selectVega->where("name", "Frape Company");
	    $vega = $selectVega->fetchObject();
	    
	    //it should be NOT Null
	    $this->assertNotNull($vega);
	    
        //finding the other.....it should not find it
	    $selectVega = $api->selectVegas();
	    $selectVega->where("name", "Vega Company");
	    $vega = $selectVega->fetchObject();
	    
	    //it should be Null
	    $this->assertNull($vega);
	}
	
    
	/**
	 * @depends testCreateUser
	 * 
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testInheritingScope($user)
	{
	    $api = new Mira("application");
	    $api->login($this->otherEmail, $this->otherPass);
	    // create a Company
	    // $google = ...new Company
	    $vegaType = $api->selectVegaTypes()->where("name", "Company")->fetchObject();
	    $google = $api->createVega($vegaType->id, "Google Company", $api->getUser());
		$google->Name = "Google";
		$number = "Number of Person";
		$google->$number = 2000;
		$date = "date started";
		$google->$date = Zend_Date::now();
		$google->country = "USA";
		$google->save();
	    
	    // create an employee
	    // $sergeyBrin = ....new Employee
	    // $sergeyBrin->scope->setInheritFrom($google->scope)
	    $vegaType = $api->selectVegaTypes()->where("name", "Contact")->fetchObject();
	    $sergey = $api->createVega($vegaType->id, "Sergey Brin", $api->getUser());
		$sergey->name = "Sergey Brin";
		$firstName = "first name";
		$sergey->$firstName = "Sergey";
		$lastName = "last name";
		$sergey->$lastName = "Brin";
		$sergey->email = "sergbrn@hotmail.com";
		$sergey->phone = 11234;
		$sergey->address = "USA";
		$sergey->save();
	    
	    // tests
	    
		//searchig the vega
	    $selectVega = $api->selectVegas();
	    $selectVega->where("name", "Sergey Brin");
	    $vega = $selectVega->fetchObject();
	    
	    //it should be NOT Null
	    $this->assertNotNull($vega);
	    
	    $api->logout();
	    $api->login($this->email, $this->pass);
	    
	    
		//searchig the vega
	    $selectVega = $api->selectVegas();
	    $selectVega->where("name", "Sergey Brin");
	    $vega = $selectVega->fetchObject();
	    
	    //it should be Null
	    $this->assertNull($vega);
	    
	    
	    //adding user to $google
		$google->scope->addUserRole($api->getUser(), "viewer");
		$google->save();
	    
	    // $sergeyBrin inherits its scope from $google
		$sergey->scope->setInheritFrom($google->scope);
		$sergey->save();
		
		//searchig the vega
	    $selectVega = $api->selectVegas();
	    $selectVega->where("name", "Sergey Brin");
	    $vega = $selectVega->fetchObject();
	    
	    //it should be Not Null
	    $this->assertNotNull($vega);
	}
	
	/**
	 * @depends testCreateUser
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status finished
	 */
	public function testPublicVisibilities()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    
	    $vega = $api->createVega($api->tfqn("Mira_Core_Contact"), "no public", $api->getUser());
	    $vega->save();
	    $privId = $vega->id; 
	    
	    $vega = $api->createVega($api->tfqn("Mira_Core_Contact"), "public view", $api->getUser());
	    $vega->scope->setPublicRole(Mira_Core_Scope::ROLE_VIEWER);
	    $vega->save();
	    $viewId = $vega->id; 

	    $vega = $api->createVega($api->tfqn("Mira_Core_Contact"), "public edit", $api->getUser());
	    $vega->scope->setPublicRole(Mira_Core_Scope::ROLE_EDITOR);
	    $vega->save();
	    $editId = $vega->id; 
	    
	    $api->logout();
	    $api->login($this->otherEmail, $this->otherPass);
	    
	    $vega = $api->vid($privId);
	    $this->assertNull($vega);
	    
	    $vega = $api->vid($viewId);
	    $this->assertNotNull($vega);
	    
	    $vega = $api->selectVegas()
	                ->where("id", $editId)
	                ->where("security", null, "editor")
	                ->fetchObject();
	    $this->assertNotNull($vega);
	}
}
