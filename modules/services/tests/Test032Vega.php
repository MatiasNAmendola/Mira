<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

class Test032Vega extends Mira_Core_Test_TestCase
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
     * @codereview_status finished
     */
	public function testInit()
	{
	    // api
        $api = new Mira("application");
        $api->login(self::CRED_EMAIL, self::CRED_PASS);
        // service
	    $sv = new Mira_Service_VegaService();
        // type
        $type = $api->createVegaType("Test032Vega", $api->getUser());
        $type->createProperty("contact", $api->tid(7)); // Contact type
        $type->createProperty("primitive", 1); // text
        $type->save();
        // some users for permissions
        $usv = new Mira_Service_UserService();
        $otherUser1 = $usv->createUser("Test032_User1@gmail.com", "pass");
        $otherUser2 = $usv->createUser("Test032_User2@gmail.com", "pass");

        return array($sv, $api, $type, $otherUser1, $otherUser2);
	}
	
	/**
	 * @depends testInit
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
	 * 
	 * @param array $params
	 */
	public function testCreate($params)
	{
	    list($sv, $api, $type, $otherUser1, $otherUser2) = $params;
	    
	    $permissions = array(
	        $otherUser1->id => "editor",
	        $otherUser2->id => "viewer"
	    );
	    $properties = array(
	        "contact" => 1,
	        "primitive" => "any text"
	    );
	    $vega = $sv->create($type->id, "Test032_Vega1", $properties, $permissions);
	    // check save
	    $this->assertNotNull($vega);
	    $this->assertNotNull($vega->id);
	    $actual = $api->vid($vega->id);
	    $this->assertNotNull($actual);
	    // check properties
	    $this->assertSame($actual->contact->id, "1");
	    $this->assertSame($actual->primitive, "any text");
	    // check security
	    $scp = $actual->scope;
	    $this->assertSame($scp->getUserRole($otherUser1), "editor");
	    $this->assertSame($scp->getUserRole($otherUser2), "viewer");
	    $this->assertSame($scp->getUserRole(333), null);
	    return $actual;
	}
	
	/**
	 * @depends testInit
	 * @depends testCreate
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
	 * 
	 * @param array $sv
	 */
	public function testUpdate($params, $vega)
	{
	    list($sv, $api, $type, $otherUser1, $otherUser2) = $params;
	    
	    $permissions = array(
	        $otherUser1->id => null,
	        $otherUser2->id => "editor"
	    );
	    $properties = array(
	        "primitive" => "another text"
	    );
	    $vega = $sv->update($vega->id, "Test032_Vega1_changed", $properties, $permissions);
	    // check save
	    $this->assertNotNull($vega);
	    $this->assertNotNull($vega->id);
	    $actual = $api->vid($vega->id);
	    $this->assertNotNull($actual);
	    // check properties
	    $this->assertSame(intval($actual->contact->id), 1);
	    $this->assertSame($actual->primitive, "another text");
	    // check security
	    $scp = $actual->scope;
	    $this->assertSame($scp->getUserRole($otherUser1), null);
	    $this->assertSame($scp->getUserRole($otherUser2), "editor");
	    $this->assertSame($scp->getUserRole(333), null);
	}
	
	/**
	 * @depends testInit
	 * @depends testCreate
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
	 * 
	 * @param array $sv
	 */
	public function testDelete($params, $vega)
	{
	    list($sv, $api, $type, $otherUser1, $otherUser2) = $params;
	    
	    $sv->delete($vega->id);
	    
	    $this->assertNull($api->vid($vega->id));
	}
	
	/**
	 * @depends testInit
	 * @depends testCreate
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
	 * 
	 * @param array $sv
	 */
	public function testRestore($params, $vega)
	{
	    list($sv, $api, $type, $otherUser1, $otherUser2) = $params;
	    
	    $sv->restore($vega->id);
	    
	    $this->assertNotNull($api->vid($vega->id));
	}
	
	/**
	 * @depends testInit
	 * @depends Test021SchoolSample::testCreateTypes
	 * @depends Test021SchoolSample::testCreateVegas
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status rejected
	 * 
	 * @param array $sv
	 */
	public function testFind($params, $createTypesReturn, $createVegasReturn)
	{
	    list($sv, $api, $type, $otherUser1, $otherUser2) = $params;
	    list ($t_school, $t_department, $t_teacher, $t_student) = $createTypesReturn;
        list ($v_school, list($v_herve, $v_tea_Cabaret, $v_tea_IT, $v_tea_MA), list($v_stu_SA, $v_stu_IT, $v_stu_MA), list($v_dep_SA, $v_dep_IT, $v_dep_MA), $v_regularTeachers) = $createVegasReturn;
        
        // simple id filter
        $this->assertSame(count($sv->find(array(array("id", $v_school->id)))), 1);
        
        // property filter
        $result = $sv->find(
            array(
                array("vegaType", "Student"),
                array("department", "Systemes", "permissive")
            ),
            null,
            array(
                "lazy" => true
            )
        );
        $this->assertSame(count($result), count($v_stu_SA));
        
        
        // test paging
        $result = $sv->find(
            array(
                array("vegaType", "Student")
            ),
            null,
            array(
                "page_offset" => 3,
                "page_count" => 3,
                "lazy" => true
            )
        );
        $this->assertSame(count($result), 3);
	}
	
	/**
	 * @depends testInit
	 * @depends Test021SchoolSample::testCreateTypes
	 * @depends Test021SchoolSample::testCreateVegas
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status finished
	 * 
	 * @param array $sv
	 */
	public function testLinks($params, $createTypesReturn, $createVegasReturn)
	{
	    list($sv, $api, $type, $otherUser1, $otherUser2) = $params;
	    list ($t_school, $t_department, $t_teacher, $t_student) = $createTypesReturn;
        list ($v_school, list($v_herve, $v_tea_Cabaret, $v_tea_IT, $v_tea_MA), list($v_stu_SA, $v_stu_IT, $v_stu_MA), list($v_dep_SA, $v_dep_IT, $v_dep_MA), $v_regularTeachers) = $createVegasReturn;
        
        // create a link
        $sv->addGenericLink($v_herve->id, $v_herve->revision, $v_tea_Cabaret->id);
        
        // test link creation
        $result = $sv->findLinks($v_herve->id, 0);
        $found = false;
        if ($result)
        foreach ($result as $vega) {
            if ($vega->id == $v_tea_Cabaret->id) {
                $found = true;    
                break;
            }
        }
        $this->assertTrue($found, "Link was not saved.");
        
        // remove link
        $sv->deleteGenericLink($v_herve->id, $v_herve->revision, $v_tea_Cabaret->id);
        
        // test link creation
        $result = $sv->findLinks($v_herve->id, 0);
        $found = false;
        if ($result)
        foreach ($result as $vega) {
            if ($vega->id == $v_tea_Cabaret->id) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, "Link was not deleted.");
	}
}
