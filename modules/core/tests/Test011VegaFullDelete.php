<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

class Test011VegaFullDelete extends Mira_Core_Test_TestCase
{
	
	public static function setUpBeforeClass()
    {
        self::$config = dirname(__FILE__) . '/../../../resources/tests/config.ini';
        parent::setUpBeforeClass();
    }
    
	public $email = "ayn_nian@hotmail.com";
	public $pass = "andres";
	
    public function createVegaType()
    {
        $api = new Mira("application");
        //login
        $api->login($this->email, $this->pass);
        $user = $api->getUser();
        
        // create and save the actual vegatype
		$vegaTypeRock = $api->createVegaType("Rock Band", $user);
		
    	//creating the properties
        $nameProp = $vegaTypeRock->createProperty("Group Name", 1);
        $memberProp = $vegaTypeRock->createProperty("members", 2);
        $playProp = $vegaTypeRock->createProperty("playing", 5);
        $countryProp = $vegaTypeRock->createProperty("country", 1);

		$vegaTypeRock->save();
		
		$vegaType = $api->createVegaType("Fan", $user);
		//creating the vegaType
        $nameProp = $vegaType->createProperty("Name", 1);
        $emailProp = $vegaType->createProperty("email", 1);
        $vegaTypeProp = $vegaType->createProperty("Favorite Group", $vegaTypeRock);

        // create and save the actual vegatype
		$vegaType->save();
    }
    
    
    public function createVegas()
	{
	    $api = new Mira("application");
		//creating new user
		$user = $api->createUser("mauricio","mauricio");
		$user->save();
		
		//1. creating ROCK BAND VEGA
		$vegaType = $api->tname("Rock Band");
		$vega = $api->createVega($vegaType, "Queen", $user);
		$groupName = "Group Name";
		$vega->$groupName = "Queen";
		$vega->members = 4;
		$vega->playing = true;
		$vega->country = "England";
		$vega->save();
		
		$vega = $api->createVega($vegaType, "Metallica", $user);
		$groupName = "Group Name";
		$vega->$groupName = "Metallica";
		$vega->members = 4;
		$vega->playing = true;
		$vega->country = "USA";
		$vega->save();
		
		//2. creating FAN VEGA
		$vegaType = $api->tname("Fan");
		$vega = $api->createVega($vegaType, "Freddi Mercury", $user);
		$group = "Favorite Group";
		$vega->Name = "Freddie";
		$vega->email = "freddiemercury@hotmail.com";
		$vega->$group = $api->vname("Queen");
		$vega->save();
		
		$vega = $api->createVega($vegaType, "James Hetfield", $user);
		$vega->name = "James Hetfield";
		$vega->Name = "James";
		$vega->email = "jameshetfield@hotmail.com";
		$vega->$group = $api->vname("Metallica");
		$vega->save();
	}
	
    /**
     * @codereview_owner andres
     * @codereview_reviewer karla
     * @codereview_status accepted
     */
    public function testVegaFullDelete()
    {
	    $api = new Mira("application");
	    
        $this->createVegaType();
        $this->createVegas();
        
	    $api->login("mauricio", "mauricio");
		$group = "Favorite Group";
		$groupName = "Group Name";
    	$james = $api->vname("James Hetfield");
    	$this->assertSame("Metallica",$james->$group->$groupName);
    	
    	$vega = $api->vname("Metallica");
    	$vega->fullDelete();
    	$this->assertFalse(isset($james->$group->$groupName));
    }
    
	/**
     * @codereview_owner andres
     * @codereview_reviewer karla
     * @codereview_status accepted
     */
    public function testVegaTypeFullDelete()
    {
	    $api = new Mira("application");
		$vegaType = $api->tname("Fan");
		$this->assertSame($vegaType->name,"Fan");
		
		$vegas = $api->selectVegas($vegaType)->fetchAll();
		$this->assertNotEquals(count($vegas), 0);
		
		$vegaType->fullDelete();
		//doesn't find any vega with this type
		$vegas = $api->selectVegas($vegaType)->fetchAll();
		$this->assertEquals(count($vegas), 0);

		//doesn't find any vegatype with this name
		$vegaType = $api->tname("Fan");
		$this->assertNull($vegaType);		
    }
}
