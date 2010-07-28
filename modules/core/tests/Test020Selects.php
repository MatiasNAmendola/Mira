<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

class Test020Selects extends Mira_Core_Test_TestCase
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
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testApiCreation()
    {
        $api = new Mira("application");
        $api->login($this->email, $this->pass);
        
        $api->createUser("maz.spam@gmail.com", "pass")->save();
        
        return $api;
    }

    /**
     * @depends testApiCreation
     * 
     * @param Mira
     * @return array
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testCreateTypes($api)
    {
        $vt1 = $api->createVegaType("Subtype 1", $api->getUser());
        $vt1->createProperty()->name = "property1";
        $vt1->createProperty()->name = "property2";
        $vt1->createProperty()->name = "property3";
        $vt1->createProperty()->name = "property4";
        $vt1->createProperty()->name = "property5";
        $vt1->save();
        
        $vt2 = $api->createVegaType("Subtype 2", $api->getUser());
        $vt2->createProperty()->name = "property1";
        $vt2->createProperty()->name = "property2";
        $vt2->createProperty()->name = "property3";
        $vt2->save();
        
        $vt3 = $api->createVegaType("Type 1", $api->getUser());
        $vp1 = $vt3->createProperty();
        $vp1->name = "property1";
        $vt3->createProperty()->name = "property2";
        $this->vp3 = $vp3 = $vt3->createProperty();
        $vp3->name = "property3";
        $vp3->type = $vt1;
        $vp4 = $vt3->createProperty();
        $vp4->name = "property4";
        $vp4->type = $vt2;
        $vt3->save();
        
        $vt4 = $api->createVegaType("Deleted type 1", $api->getUser());
        $vt4->save();
        $vt4->delete();
        
        return array($vt1, $vt2, $vt3, $vp1, $vp3);
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateTypes
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testCreateVegas($api, $createTypesReturn)
    {
        list($vt1, $vt2, $vt3, $vp1, $vp3) = $createTypesReturn;
        
        $owner = $api->getUser();
        
        $a = $this->createVT1Vega($api, $vt1, $owner, "vega a", "a1", "a2", "a3", "a4", "a5");
        $b = $this->createVT1Vega($api, $vt1, $owner, "vega b", "b1", "b2", "b3", "b4", "b5", "viewer");
        $c = $this->createVT1Vega($api, $vt1, $owner, "vega c", "c1", "c2", "c3", "c4", "c5", "editor");
        $d = $this->createVT1Vega($api, $vt1, $owner, "vega d", "d1", "d2", "d3", "d4", "d5", "editor");
        
        $e = $this->createVT2Vega($api, $vt2, $owner, "vega e", "e1", "e2", "e3", "e4", "e5");
        $f = $this->createVT2Vega($api, $vt2, $owner, "vega f", "f1", "f2", "f3", "f4", "f5");
        $f->delete();
        $g = $this->createVT2Vega($api, $vt2, $owner, "vega g", "g1", "g2", "g3", "g4", "g5");
        
        $h = $this->createVT3Vega($api, $vt3, $owner, "vega h", "h1", "h2", $a, $e);
        $i = $this->createVT3Vega($api, $vt3, $owner, "vega i", "i1", "i2", $b, $e);
        $j = $this->createVT3Vega($api, $vt3, $owner, "vega j", "j1", "j2", $d, $g);
        
        return array($a, $e, $h, $i);
    }
    
    private function createVT1Vega($api, $type, $owner, $name, $p1, $p2, $p3, $p4, $p5, $addUserScope = false)
    {
        $vega = $type->createVega();
        $vega->name = $name;
        $vega->owner = $owner;
        $vega->property1 = $p1;
        $vega->property2 = $p2;
        $vega->property3 = $p3;
        $vega->property4 = $p4;
        $vega->property5 = $p5;
        if ($addUserScope !== false) {
            $maz = $api->uemail("maz.spam@gmail.com");
            $vega->scope->addUserRole($maz, $addUserScope);
        }
        $vega->save();
        return $vega;
    }
    
    private function createVT2Vega($api, $type, $owner, $name, $p1, $p2, $p3)
    {
        $vega = $type->createVega();
        $vega->name = $name;
        $vega->owner = $owner;
        $vega->property1 = $p1;
        $vega->property2 = $p2;
        $vega->property3 = $p3;
        $vega->save();
        return $vega;
    }
    
    private function createVT3Vega($api, $type, $owner, $name, $p1, $p2, $p3, $p4)
    {
        $vega = $type->createVega();
        $vega->name = $name;
        $vega->owner = $owner;
        $vega->property1 = $p1;
        $vega->property2 = $p2;
        $vega->property3 = $p3;
        $vega->property4 = $p4;
        $vega->save();
        return $vega;
    }
    
    
    // ###################################################
    // TESTS
    // ###################################################

    /**
     * @depends testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testVegaById($api, $createTypesReturn, $createVegasReturn)
    {
        list ($vt1, $vt2, $vt3, $vp1, $vp3) = $createTypesReturn;
        list ($a, $e, $h, $i) = $createVegasReturn;
        
        $select = $api->selectVegas();
        $select->where("id", $a->id);
        $result = $select->fetchObject();
        $this->assertSame($result->name, $a->name);
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testCountVegas($api, $createTypesReturn, $createVegasReturn)
    {
        list ($vt1, $vt2, $vt3, $vp1, $vp3) = $createTypesReturn;
        list ($a, $e, $h, $i) = $createVegasReturn;
        
        $count = $api->selectVegas($vt1)->count();
        $this->assertSame($count, 4);
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testTypeById($api, $createTypesReturn, $createVegasReturn)
    {
        list ($vt1, $vt2, $vt3, $vp1, $vp3) = $createTypesReturn;
        list ($a, $e, $h, $i) = $createVegasReturn;
        
        $select = $api->selectVegaTypes();
        $select->where("id", $vt1->id);
        $result = $select->fetchObject();
        $this->assertSame($result->name, $vt1->name);
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testVegaByName($api, $createTypesReturn, $createVegasReturn)
    {
        list ($vt1, $vt2, $vt3, $vp1, $vp3) = $createTypesReturn;
        list ($a, $e, $h, $i) = $createVegasReturn;
        
        $select = $api->selectVegas();
        $select->where("name", $a->name); // strict by default
        $result = $select->fetchObject();
        $this->assertSame($result->name, $a->name);
        
        $select = $api->selectVegas();
        $select->where("name", "ga a", "permissive");
        $result = $select->fetchObject();
        $this->assertSame($result->name, $a->name);
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testTypeByName($api, $createTypesReturn, $createVegasReturn)
    {
        list ($vt1, $vt2, $vt3, $vp1, $vp3) = $createTypesReturn;
        list ($a, $e, $h, $i) = $createVegasReturn;
        
        $select = $api->selectVegaTypes();
        $select->where("name", $vt1->name); // strict by default
        $result = $select->fetchObject();
        $this->assertSame($result->name, $vt1->name);
        
        $select = $api->selectVegaTypes();
        $select->where("name", "pe 1", "permissive");
        $result = $select->fetchObject();
        $this->assertSame($result->name, $vt1->name);
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testVegaByType($api, $createTypesReturn, $createVegasReturn)
    {
        list ($vt1, $vt2, $vt3, $vp1, $vp3) = $createTypesReturn;
        list ($a, $e, $h, $i) = $createVegasReturn;
        
        $type1Instances = 4;
        
        // type's vo
        $select = $api->selectVegas();
        $select->where("vegaType", $vt1);
        $results = $select->fetchAll();
        $this->assertTrue(count($results) == $type1Instances);
        
        // type's id
        $select = $api->selectVegas();
        $select->where("vegaType", $vt1->id);
        $results = $select->fetchAll();
        $this->assertTrue(count($results) == $type1Instances);
        
        // type's name
        $select = $api->selectVegas();
        $select->where("vegaType", $vt1->name);
        $results = $select->fetchAll();
        $this->assertTrue(count($results) == $type1Instances);
        
        // type's select
        $typeSelect = $api->selectVegaTypes();
        $typeSelect->where("id", $vt1->id);
        $select = $api->selectVegas();
        $select->where("vegaType", $typeSelect);
        $results = $select->fetchAll();
        $this->assertTrue(count($results) == $type1Instances);
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testVegaBySecurity($api, $createTypesReturn, $createVegasReturn)
    {
        list ($vt1, $vt2, $vt3, $vp1, $vp3) = $createTypesReturn;
        list ($a, $e, $h, $i) = $createVegasReturn;
        
        // api has to have system level for that one
        $api = Zend_Registry::get(Mira_Core_Constants::REG_API);
        
        $maz = $api->uemail("maz.spam@gmail.com");
            
        $select = $api->selectVegas()
                      ->where("vegaType", $vt1)
                      ->where("security", $maz, "viewer");
        $results = $select->fetchAll();
        $this->assertSame(count($results), $v = 3);
        
        $select = $api->selectVegas()
                      ->where("vegaType", $vt1)
                      ->where("security", $maz, "editor");
        $results = $select->fetchAll();
        $this->assertSame(count($results), $e = 2);
        
        $select = $api->selectVegas()
                      ->where("vegaType", $vt1)
                      ->where("security", $maz, "viewer_only");
        $results = $select->fetchAll();
        $this->assertSame(count($results), $v - $e);
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testVegaByStatus($api, $createTypesReturn, $createVegasReturn)
    {
        list ($vt1, $vt2, $vt3, $vp1, $vp3) = $createTypesReturn;
        list ($a, $e, $h, $i) = $createVegasReturn;
        
        $select = $api->selectVegas();
        $select->where("vegaType", $vt2)
               ->where("status", "alive");
        $results = $select->fetchAll();
        $this->assertSame(($alive = count($results)), 2);
        
        $select = $api->selectVegas();
        $select->where("vegaType", $vt2)
               ->where("status", "trashed");
        $results = $select->fetchAll();
        $this->assertSame(($trashed = count($results)), 1);
        
        $select = $api->selectVegas();
        $select->where("vegaType", $vt2)
               ->where("status", "any");
        $results = $select->fetchAll();
        $this->assertSame(count($results), $trashed + $alive);
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testTypeByStatus($api, $createTypesReturn, $createVegasReturn)
    {
        list ($vt1, $vt2, $vt3, $vp1, $vp3) = $createTypesReturn;
        list ($a, $e, $h, $i) = $createVegasReturn;
        
        $select = $api->selectVegaTypes();
        $select->where("status", "alive");
        $results = $select->fetchAll();
        $alive = count($results);
        
        $select = $api->selectVegaTypes();
        $select->where("status", "trashed");
        $results = $select->fetchAll();
        $trashed = count($results);
        
        $select = $api->selectVegaTypes();
        $select->where("status", "any");
        $results = $select->fetchAll();
        $this->assertSame(count($results), $trashed + $alive);
        
        $select = $api->selectVegaTypes();
        $select->where("name", "deleted", "permissive");
        $results = $select->fetchAll();
        $this->assertSame(count($results), 0);
        
        $select = $api->selectVegaTypes();
        $select->where("name", "deleted", "permissive")
               ->where("status", "trashed");
        $results = $select->fetchAll();
        $this->assertSame(count($results), 1);
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testVegaByPrimitiveProperty($api, $createTypesReturn, $createVegasReturn)
    {
        list ($vt1, $vt2, $vt3, $vp1, $vp3) = $createTypesReturn;
        list ($a, $e, $h, $i) = $createVegasReturn;
        
        // select by the property name
        $select = $api->selectVegas();
        $select->where("property5", $a->property5);
        $result = $select->fetchObject();
        $this->assertSame($result->name, $a->name);

        // select by the property id
        $select = $api->selectVegas();
        $select->where("vegaType", $vt2)
               ->where("property1", $e->property1)
               ->where("property2", $e->property2);
        $result = $select->fetchObject();
        $this->assertSame($result->name, $e->name);
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testVegaByVegaProperty($api, $createTypesReturn, $createVegasReturn)
    {
        list ($vt1, $vt2, $vt3, $vp1, $vp3) = $createTypesReturn;
        list ($a, $e, $h, $i) = $createVegasReturn;
        
        // select by the property name
        $select = $api->selectVegas();
        $select->where("vegaType", $vt3)
               ->where("property4", $e);
        $results = $select->fetchAll(true);
        $this->assertSame(count($results), 2);
        
        // mix prims and vega
        $select = $api->selectVegas();
        $select->where("vegaType", $vt3)
               ->where("property1", "h", "permissive")
               ->where("property4", "ga e", "permissive");
        $result = $select->fetchObject(true);
        $this->assertSame($result->name, $h->name);
    }
    
    /**
     * @depends testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testComplex($api, $createTypesReturn, $createVegasReturn)
    {
        list ($vt1, $vt2, $vt3, $vp1, $vp3) = $createTypesReturn;
        list ($a, $e, $h, $i) = $createVegasReturn;
        // select by the property name
        $select = $api->selectVegas();
        $select->where("vegaType", $vt3)
               ->where("security", $api->getUser(), "viewer")
               ->where("property4", 
                $api->selectVegas()
                    ->where("security", null, "viewer")
                    ->where("property2", "e2")
                    ->where("vegaType", $vt2)
                    );
        $results = $select->fetchAll();
        $this->assertSame(count($results), 2);
    }
}