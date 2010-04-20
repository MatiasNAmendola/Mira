<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

class Test033VegaType extends Mira_Core_Test_TestCase
{
	
	public static function setUpBeforeClass()
    {
        self::$sqldump = dirname(__FILE__) . '/../../../resources/tests/dump.sql';
        self::$config = dirname(__FILE__) . '/../../../resources/tests/config.ini';
        parent::setUpBeforeClass();
    }
    
	const CRED_EMAIL = "ayn_nian@hotmail.com";
	const CRED_PASS = "andres";
    
    /**
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
     */
	public function testInit()
	{
	    // api
        $api = new Mira("application");
        $api->login(self::CRED_EMAIL, self::CRED_PASS);
        // service
        $sv = new Mira_Service_VegaTypeService();

        return array($sv, $api);
	}
	
	/**
	 * @depends testInit
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status rejected
	 * 
	 * @param array $params
	 */
	public function testCreate($params)
	{
	    list($sv, $api) = $params;
	    $properties = array(
	        array("name" => "prop 1", 	"primitiveType" => 1), // Simple Text
	        array("name" => "prop 2", 	"primitiveType" => "Simple text"),
	        array("name" => "contact",  "vegaType" => 7)
	    );
	    
	    $type = $sv->create("Test033_VegaType1", $properties);
	    $this->assertNotNull($type);
	    
	    $actual = $api->tname("Test033_VegaType1");
	    $this->assertNotNull($actual);
	    
	    $p = $actual->propertyWithName("prop 1");
	    $this->assertNotNull($p);
	    $this->assertEquals($p->position, 0);
	    $this->assertEquals($p->type, 1);
	    
	    $p = $actual->propertyWithName("prop 2");
	    $this->assertNotNull($p);
	    $this->assertEquals($p->position, 1);
	    $this->assertEquals($p->type, 1);
	    
	    $p = $actual->propertyWithName("contact");
	    $this->assertNotNull($p);
	    $this->assertEquals($p->position, 2);
	    $this->assertEquals($p->type->id, 7);
	    
	    return $actual;
	}
	
	/**
	 * @depends testInit
	 * @depends testCreate
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status rejected
	 * 
	 * @param array $params
	 * @param Mira_Core_VegaType $type
	 */
	public function testUpdate($params, $type)
	{
	    list($sv, $api) = $params;
	    
	    $properties = array(
	        "prop 1" => array("name" => "property 1", "position" => 4), // Simple Text
	        "prop 2" => null
	    );
	    
	    $type = $sv->update($type->id, "Test033_VegaType1 changed", $properties);
	    $this->assertNotNull($type);
	    
	    $actual = $api->tname("Test033_VegaType1 changed");
	    $this->assertNotNull($actual);
	    
	    $p = $actual->propertyWithName("prop 1");
	    $this->assertNull($p);

	    $p = $actual->propertyWithName("property 1");
	    $this->assertNotNull($p);
	    $this->assertEquals($p->position, 4);
	    $this->assertEquals($p->type, 1);
	    
	    $p = $actual->propertyWithName("prop 2");
	    $this->assertNull($p);
	    
	    $p = $actual->propertyWithName("contact");
	    $this->assertNotNull($p);
	    $this->assertEquals($p->position, 2);
	    $this->assertEquals($p->type->id, 7);
	    
	    return $actual;
	}
	
	/**
	 * @depends testInit
	 * @depends testCreate
	 * @depends Test021SchoolSample::testCreateTypes
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
	 * 
	 * @param array $params
	 * @param Mira_Core_VegaType $type
	 */
	public function testFind($params, $type, $createTypesReturn)
	{
	    list($sv, $api) = $params;
	    list ($t_school, $t_department, $t_teacher, $t_student) = $createTypesReturn;
	    
	    // simple id filter
        $this->assertSame(count($sv->find(array(array("id", $t_school->id)))), 1);
        
        // property filter
        $result = $sv->find(
            array(
                array("name", "ent", "permissive"),
                array("name", "Dep", "permissive")
            ),
            null,
            array(
                "lazy" => true
            )
        );
        $this->assertSame(count($result), 1);
        $r = $result[0];
        $this->assertSame($r->name, "Department");
        
        
        // test paging
        $result = $sv->find(
            null,
            array(
                "page_offset" => 3,
                "page_count" => 3,
                "lazy" => true
            )
        );
        $this->assertSame(count($result), 3);
	}
}
