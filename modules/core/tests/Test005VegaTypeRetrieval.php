<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

class Test005VegaTypeRetrieval extends Mira_Core_Test_TestCase
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
     * @codereview_owner andres
     * @codereview_reviewer karla
     * @codereview_status accepted
     */
	public function testRetrievalbyName()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
	    
		$vegaType = $api->tname("Contact");
		$this->assertSame($vegaType->name,"Contact");
		$this->assertEquals($vegaType->id,7);
		$this->assertSame($vegaType->fqn_vgt,"Mira_Core_Contact");

		$vegaType = $api->selectVegaTypes()
		            ->where("fqn", "Mira_Core_Contact")
		            ->fetchObject();
		$this->assertSame($vegaType->name,"Contact");
		$this->assertEquals($vegaType->id,7);
		$this->assertSame($vegaType->fqn,"Mira_Core_Contact");
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
	    
		$vegaType = $api->tid(7);
		$this->assertSame($vegaType->name,"Contact");
		$this->assertEquals($vegaType->id,7);
		$this->assertSame($vegaType->fqn,"Mira_Core_Contact");
	}
	
	/**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
    public function testRetrievalAll()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
	    $user = $api->getUser();
		$vegaTypes = $api->selectVegaTypes()->fetchAll();
		$this->assertNotNull($vegaTypes);
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
	    
		$vegaType = $api->selectVegaTypes()->where("name", "Cont", "permissive")->fetchObject();
		
		$this->assertSame($vegaType->name,"Contact");
		$this->assertEquals($vegaType->id,7);
		$this->assertSame($vegaType->fqn,"Mira_Core_Contact");
		
		$vegaType = $api->selectVegaTypes()->where("fqn", "Mira_Core_Contact")->fetchObject();
		$this->assertSame($vegaType->name,"Contact");
		$this->assertEquals($vegaType->id,7);
		$this->assertSame($vegaType->fqn,"Mira_Core_Contact");
	}
	
}
