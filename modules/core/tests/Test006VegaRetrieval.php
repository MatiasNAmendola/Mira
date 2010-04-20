<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';
require_once 'Test002VegaTypeCreation.php';

class Test006VegaRetrieval extends Mira_Core_Test_TestCase
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
	public function testRetrievalbyName()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
	    
		$vega = $api->vname("Andres PENA");
		$this->assertSame($vega->name,"Andres PENA");
		$this->assertEquals($vega->id,1);
		$this->assertSame($vega->email,"ayn_nian@hotmail.com");
	}
	
	
     /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testRetrievalbyIntelliName()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
		//find a vega by NameIntelli and vegaTypeId
		$vegaType = $api->tname("Contact");
		$vega = $api->selectVegas($vegaType)
		        ->where("name", "Andres P", "permissive")
		        ->fetchAll();
		$this->assertSame($vega[0]->name,"Andres PENA");
		$this->assertEquals($vega[0]->id,1);
		$this->assertSame($vega[0]->email,"ayn_nian@hotmail.com");
		
		//find a vega just by NameIntelli
		$vega = $api->selectVegas()
		        ->where("name", "Andres P", "permissive")
		        ->fetchAll();
		$this->assertSame($vega[0]->name,"Andres PENA");
		$this->assertEquals($vega[0]->id,1);
		$this->assertSame($vega[0]->email,"ayn_nian@hotmail.com");
	}
	
     /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testRetrievalbyId()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
	    
		$vega = $api->vid(1);
		$this->assertSame($vega->name, "Andres PENA");
		$this->assertEquals($vega->id, 1);
		$this->assertSame($vega->email, "ayn_nian@hotmail.com");
	}
	
     /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testRetrievalbyProperty()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
		$vegaType = $api->tname("Contact");
		$prp = $vegaType->propertyWithName("email");
		$vega = $api->selectVegas($vegaType)
		        ->where($prp->id, "ayn_nian@hotmail.com")
		        ->fetchAll();
		$this->assertSame($vega[0]->name,"Andres PENA");
	}
	
     /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testRetrievalbyDeleted()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
		$vegaType = $api->tname("Contact");
		
		//creating and deleting vega to retrieval by deleted
		$vega = $api->createVega($vegaType, "will be deleted", $user);
		$vega->email = "otrofanaticodelossimpsons@hotmail.com";
		$vega->save();
		$vega->delete();
		
		$vega = $api->selectVegas($vegaType)
		        ->where("name", "will be deleted")
		        ->where("status", "trashed")
		        ->fetchObject();
		$this->assertSame($vega->name,"will be deleted");
		$this->assertSame($vega->email,"otrofanaticodelossimpsons@hotmail.com");
		$vega->restore();
	}	
	
	/**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testRetrievalByRelated()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
		//creating the vegaType
		
        $contactVegaType = $api->tname("Contact");
        
        $vegaType = $api->createVegaType("Winners", $user);
        
        $sportProp = $vegaType->createProperty();
        $sportProp->name = "sport";
        $sportProp->position = 1;
        $sportProp->type = 1;
        
        $vsProp = $vegaType->createProperty();
        $vsProp->name = "vs";
        $vsProp->position = 2;
        $vsProp->type = $contactVegaType;
        
        $vegaTypeProp = $vegaType->createProperty();
        $vegaTypeProp->name = "winner";
        $vegaTypeProp->position = 3;
        $vegaTypeProp->type = $contactVegaType;
		
        // create and save the actual vegatype
		$vegaType->save();
		
		
		$vega = $api->createVega($contactVegaType, "MAthieu L", $user->id);
		$firstName = "first name";
		$vega->$firstName = "Mathieu";
		$lastName = "last name";
		$vega->$lastName = "Lemaire";
		$vega->email = "lemairemathieu@hotmail.com";
		$vega->phone = 318800;
		$vega->address = "France";
		$vega->save();		
		
		
		$vegaType = $api->tname("Winners");
		$vega = $api->createVega($vegaType, "Andres", $user->id);
		$vega->sport = "pool";
		$vega->vs = $api->vname("MAthieu L"); // @wrong @rejected @bad
		$vega->winner = $api->vid(1);
		$vega->save();
		
		
		//test a link TO (vega->id = 1)
		$vegasLink = $api->selectVegas()->linkedTo(1);
		$this->assertNotNull($vegasLink);
		
		
		//test a link FROM (vega->name = Andres)
		$vegasLinked = $api->selectVegas()->linkedTo($vega->id);
		//vega->vs and vega->winner
		$this->assertEquals(count($vegasLinked),1);//mientras antes era 2
		
		
		//creating a simple link
		$toVega = $api->vname("MAthieu L");
		$vega = $api->vid(1);
		$vega->addGenericLink($toVega->id);
		$vegasLink2 = $api->selectVegas()->linkedTo(1);
		
		
		//vegaLink = before add the simple link, vegalink2 = after add the simple link
		//vegaLink2 > vegaLink
		$this->assertEquals(count($vegasLink2),count($vegasLink));//mientras antes se le sumaba 1 al ultimo
	}
	
     /**
     * @codereview_owner andres
     * @codereview_reviewer karla
     * @codereview_status accepted
     */
	public function testFindByNameWithTypeId()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
	    
		$vegaType = $api->tname("Contact");
		$vega = $api->selectVegas($vegaType)
				->where("name", "MAthieu L")
				->fetchObject();
		$this->assertNotNull($vega->id);
	}
	
	
    /**
    * @codereview_owner andres
    * @codereview_reviewer maz
    * @codereview_status accepted
    */
	public function testFindByNameAndLazy()
	{		
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
	    
		$vegaType = $api->tname("Contact");
		$vega = $api->selectVegas($vegaType)
				->where("name", "MAthieu L")
				->fetchObject(true);
		$this->assertSame($vega->name, "MAthieu L");
		$this->assertFalse(isset($vega->email));
	}
}
