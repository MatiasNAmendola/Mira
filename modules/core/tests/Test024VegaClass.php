<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

set_include_path(
    dirname(__FILE__) . '/models' .  PATH_SEPARATOR . 
    get_include_path());

class Test024VegaClass extends Mira_Core_Test_TestCase
{
	
	public static function setUpBeforeClass()
    {
        self::$config = dirname(__FILE__) . '/../../../resources/tests/config.ini';
        parent::setUpBeforeClass();
    }
    
	public $email = "ayn_nian@hotmail.com";
	public $pass = "andres";
	
    // ###################################################
    // INITIALISATION
    // ###################################################
    
    /**
     * @codereview_owner andres
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testApiCreation()
    {
        $api = new Mira("application");
        $api->login($this->email, $this->pass);
        return $api;
    }

    /**
     * @depends testApiCreation
     * 
     * @param Mira
     * @return array
     * 
     * @codereview_owner andres
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testCreateType($api)
    {
        $vt = $api->createVegaType("Computers", $api->getUser());
        $vt->fqn = "Computer";
        $name = $vt->createProperty();
        $name->name = "kind";
        $name->type = 1;
        $floors = $vt->createProperty();
        $floors->name = "mark";
        $floors->type = 1;
        $vt->save();
        
        $sel = $api->selectVegaTypes()->where("id", $vt->id);
        $vt = $sel->fetchObject();
        $this->assertSame($vt->name, "Computers");
        $this->assertSame($vt->fqn, "Computer");
        return $vt;
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateType
     * 
     * @codereview_owner andres
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testCreateVegas($api, $vt)
    {
        $vg = $api->createVega($vt, "computers", $api->getUser());
        $vg->kind = "laptop";
        $vg->mark = "hp";
        $vg->save();
        
        $vg = $api->createVega($vt, "computers", $api->getUser());
        $vg->kind = "laptop";
        $vg->mark = "compaq";
        $vg->save();
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateType
     * 
     * @codereview_owner andres
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testNewVegas($api, $vt)
    {
        $vg = $api->createVega($vt, "Test", $api->getUser());
        $this->assertSame(get_class($vg), "Computer");
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateType
     * 
     * @codereview_owner andres
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testTypedVegas($api, $vt)
    {
        $sel = $api->selectVegas()->where("mark", "hp");
        $vg = $sel->fetchObject();
        
        $this->assertSame($vg->mark, "hp");
        $this->assertSame($vg->kind, "laptop");
        $this->assertSame(get_class($vg), "Computer");
        
        $sel = $api->selectVegas()->where("kind", "laptop");
        $vgs = $sel->fetchAll();
        
        foreach ($vgs as $vg)
        {
            $this->assertSame($vg->kind, "laptop");
            $this->assertSame(get_class($vg), "Computer"); 
        }
    }
    
    
    /**
     * @depends testApiCreation
     * @depends testCreateType
     * 
     * @codereview_owner andres
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testUntypedVegas($api, $vt)
    {
        // test that if there are no Class implementation for a certain
        // vega type, that the result is still a standard Mira_Core_Vega 
        $newType = $api->createVegaType("UntypedVegaType", $api->getUser());
        $newType->save();
        $vg = $newType->createVega("UntypedVega", $api->getUser());
        $this->assertSame(get_class($vg), "Mira_Core_Vega");
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateType
     * 
     * @codereview_owner andres
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testGetFunction($api, $vt)
    {
        $sel = $api->selectVegas()->where("mark", "hp");
        $vg = $sel->fetchObject();
        
        
        $this->assertSame(get_class($vg), "Computer");
        //it is a string hplaptop
        $this->assertSame($vg->getFullString(), "hplaptop");
    }
    
}