<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';
require_once 'Test020Selects.php';

class Test021SchoolSample extends Mira_Core_Test_TestCase
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
     * @depends Test020Selects::testApiCreation
     * 
     * @return array
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testCreateTypes($api)
    {
        $owner = $api->getUser();
        
        // create the teacher
        $t_teacher = $api->createVegaType("Teacher", $owner);
        $t_teacher->createProperty("employed since");
        $t_teacher->createProperty("salary");
        $t_teacher->save();
        
        // create the school
        $t_school = $api->createVegaType("School", $owner);
        $t_school->createProperty("built in");
        $t_school->createProperty("director", $t_teacher);
        $t_school->save();
        
        // create the department
        $t_department = $api->createVegaType("Department", $owner);
        $t_department->createProperty("director", $t_teacher);
        $t_department->createProperty("school", $t_school);
        $t_department->save();
        
        // add department to teacher (create inf loop)
        $t_teacher->createProperty("department", $t_department);
        $t_teacher->save();
        
        // create the student
        $t_student = $api->createVegaType("Student", $owner);
        $t_student->createProperty("department", $t_department);
        $t_student->createProperty("average mark");
        $t_student->save();

        return array($t_school, $t_department, $t_teacher, $t_student);
    }
    
    /**
     * @depends Test020Selects::testApiCreation
     * @depends testCreateTypes
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testCreateVegas($api, $createTypesReturn)
    {
        list($t_school, $t_department, $t_teacher, $t_student) = $createTypesReturn;
        
        $owner = $api->getUser();
        
        $v_herve = $this->createTeacher($t_teacher, $owner, "Herve BIAUSSER", 1970, 120000);
        $v_school = $this->createSchool($t_school, $owner, "Ecole Centrale de Paris", 1829, $v_herve);
        
        $v_tea_Cabaret = $this->createTeacher($t_teacher, $owner, "Laurent CABARET", 1990, 70000);
        $v_dep_SA = $this->createDepartment($t_department, $owner, "Option Systemes Avances", $v_tea_Cabaret, $v_school);
        $v_stu_SA = array();
        $v_stu_SA[] = $this->createStudent($t_student, $owner, "Student SA 1", $v_dep_SA, rand(0, 20));
        $v_stu_SA[] = $this->createStudent($t_student, $owner, "Student SA 2", $v_dep_SA, rand(0, 20));
        $v_stu_SA[] = $this->createStudent($t_student, $owner, "Student SA 3", $v_dep_SA, rand(0, 20));
        $v_stu_SA[] = $this->createStudent($t_student, $owner, "Student SA 4", $v_dep_SA, rand(0, 20));
        $v_stu_SA[] = $this->createStudent($t_student, $owner, "Student SA 5", $v_dep_SA, rand(0, 20));
        $v_stu_SA[] = $this->createStudent($t_student, $owner, "Student SA 6", $v_dep_SA, rand(0, 20));
        
        $v_tea_IT = $this->createTeacher($t_teacher, $owner, "Dir INFO", 1975, 20000);
        $v_dep_IT = $this->createDepartment($t_department, $owner, "Option Info et Telecoms", $v_tea_IT, $v_school);
        $v_stu_IT = array();
        $v_stu_IT[] = $this->createStudent($t_student, $owner, "Student IT 1", $v_dep_IT, rand(0, 20));
        $v_stu_IT[] = $this->createStudent($t_student, $owner, "Student IT 2", $v_dep_IT, rand(0, 20));
        $v_stu_IT[] = $this->createStudent($t_student, $owner, "Student IT 3", $v_dep_IT, rand(0, 20));
        
        $v_tea_MA = $this->createTeacher($t_teacher, $owner, "Dir MATHS", 1975, 20000);
        $v_dep_MA = $this->createDepartment($t_department, $owner, "Option Maths Appliquees", $v_tea_MA, $v_school);
        $v_stu_MA = array();
        $v_stu_MA[] = $this->createStudent($t_student, $owner, "Student MA 1", $v_dep_MA, rand(0, 20));
        $v_stu_MA[] = $this->createStudent($t_student, $owner, "Student MA 2", $v_dep_MA, rand(0, 20));
        $v_stu_MA[] = $this->createStudent($t_student, $owner, "Student MA 3", $v_dep_MA, rand(0, 20));
        $v_stu_MA[] = $this->createStudent($t_student, $owner, "Student MA 4", $v_dep_MA, rand(0, 20));
        $v_stu_MA[] = $this->createStudent($t_student, $owner, "Student MA 5", $v_dep_MA, rand(0, 20));
        $v_stu_MA[] = $this->createStudent($t_student, $owner, "Student MA 6", $v_dep_MA, rand(0, 20));
        $v_stu_MA[] = $this->createStudent($t_student, $owner, "Student MA 7", $v_dep_MA, rand(0, 20));
        
        $v_regularTeachers = array();
        $v_regularTeachers[] = $this->createTeacher($t_teacher, $owner, "Regular Teacher 1", 0, 0);
        $v_regularTeachers[] = $this->createTeacher($t_teacher, $owner, "Regular Teacher 2", 0, 0);
        $v_regularTeachers[] = $this->createTeacher($t_teacher, $owner, "Regular Teacher 3", 0, 0);
        $v_regularTeachers[] = $this->createTeacher($t_teacher, $owner, "Regular Teacher 4", 0, 0);
        $v_regularTeachers[] = $this->createTeacher($t_teacher, $owner, "Regular Teacher 5", 0, 0);
        $v_regularTeachers[] = $this->createTeacher($t_teacher, $owner, "Regular Teacher 6", 0, 0);
        
        return array(
                $v_school, 
                array($v_herve, $v_tea_Cabaret, $v_tea_IT, $v_tea_MA),
                array($v_stu_SA, $v_stu_IT, $v_stu_MA),
                array($v_dep_SA, $v_dep_IT, $v_dep_MA),
                $v_regularTeachers
                );
    }
    
    private function createSchool($type, $owner, $name, $builtIn, $director)
    {
        $vega = $type->createVega($name, $owner);
        $vega->__set("built in", $builtIn);
        $vega->director = $director;
        $vega->save();
        return $vega;
    }
    
    private function createDepartment($type, $owner, $name, $director, $school)
    {
        $vega = $type->createVega($name, $owner);
        $vega->director = $director;
        $vega->school = $school;
        $vega->save();
        return $vega;
    }
    
    private function createStudent($type, $owner, $name, $department, $avgMark)
    {
        $vega = $type->createVega($name, $owner);
        $vega->department = $department;
        $vega->__set("average mark", 1920);
        $vega->save();
        return $vega;
    }
    
    private function createTeacher($type, $owner, $name, $since, $salary, $department = null)
    {
        $vega = $type->createVega($name, $owner);
        $vega->__set("employed since", 1920);
        $vega->salary = $salary;
        $vega->department = $department;
        $vega->save();
        return $vega;
    }
    
    
    // ###################################################
    // TESTS
    // ###################################################

    /**
     * @depends Test020Selects::testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testSelectStudentByDep($api, $createTypesReturn, $createVegasReturn)
    {
        list ($t_school, $t_department, $t_teacher, $t_student) = $createTypesReturn;
        list ($v_school, list($v_herve, $v_tea_Cabaret, $v_tea_IT, $v_tea_MA), list($v_stu_SA, $v_stu_IT, $v_stu_MA), list($v_dep_SA, $v_dep_IT, $v_dep_MA), $v_regularTeachers) = $createVegasReturn;
        
        $sel = $api->selectVegas();
        $result = $sel->where("vegaType", "Student")
                      ->where("department", "Systemes", "permissive")
                      ->fetchAll();
        $this->assertSame(count($result), count($v_stu_SA));
        
        $result = $api->selectVegas()
                      ->where("vegaType", "Student")
                      ->where("department", $v_dep_IT)
                      ->fetchAll();
        $this->assertSame(count($result), count($v_stu_IT));
    }

    /**
     * @depends Test020Selects::testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testSelectStudentByDepAndMark($api, $createTypesReturn, $createVegasReturn)
    {
        list ($t_school, $t_department, $t_teacher, $t_student) = $createTypesReturn;
        list ($v_school, list($v_herve, $v_tea_Cabaret, $v_tea_IT, $v_tea_MA), list($v_stu_SA, $v_stu_IT, $v_stu_MA), list($v_dep_SA, $v_dep_IT, $v_dep_MA), $v_regularTeachers) = $createVegasReturn;
                
        $select = $api->selectVegas()
                      ->where("vegaType", "Student")
                      ->where("department", "SA", "permissive")
                      ->where("average mark", 10)
                      ->fetchAll(); 
        // can't test coz random
    }

    /**
     * @depends Test020Selects::testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testSelectStudentByDirector($api, $createTypesReturn, $createVegasReturn)
    {
        list ($t_school, $t_department, $t_teacher, $t_student) = $createTypesReturn;
        list ($v_school, list($v_herve, $v_tea_Cabaret, $v_tea_IT, $v_tea_MA), list($v_stu_SA, $v_stu_IT, $v_stu_MA), list($v_dep_SA, $v_dep_IT, $v_dep_MA), $v_regularTeachers) = $createVegasReturn;
                
        $result = $api->selectVegas()
                      ->where("vegaType", "Student")
                      ->where("department", 
                            $api->selectVegas()
                                ->where("vegaType", "Department")
                                ->where("director", "Laurent", "permissive"))
                      ->fetchAll();
        $this->assertSame(count($result), count($v_stu_SA));
    }

    /**
     * @depends Test020Selects::testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testSelectTeachersDirector($api, $createTypesReturn, $createVegasReturn)
    {
        list ($t_school, $t_department, $t_teacher, $t_student) = $createTypesReturn;
        list ($v_school, list($v_herve, $v_tea_Cabaret, $v_tea_IT, $v_tea_MA), list($v_stu_SA, $v_stu_IT, $v_stu_MA), list($v_dep_SA, $v_dep_IT, $v_dep_MA), $v_regularTeachers) = $createVegasReturn;
                
        $selAll = $api->selectVegas();
        $resultAll = $selAll->where("vegaType", "Teacher")
                            ->fetchAll();
                      
        $selDir = $api->selectVegas();
        $resultDir = $selDir->where("vegaType", "Teacher")
                            ->linkedTo(
                                  $api->selectVegas()->where("vegaType", "Department"),
                                  "director",
                                  "to")
                            ->fetchAll();
                         
        $this->assertSame(count($resultAll) - count($v_regularTeachers) - 1, count($resultDir));
    }
    
    /**
     * @depends Test020Selects::testApiCreation
     * @depends testCreateTypes
     * @depends testCreateVegas
     * 
     * @codereview_owner maz
     * @codereview_reviewer farf
     * @codereview_status accepted
     */
    public function testGenericLinks($api, $createTypesReturn, $createVegasReturn)
    {
        list ($t_school, $t_department, $t_teacher, $t_student) = $createTypesReturn;
        list ($v_school, list($v_herve, $v_tea_Cabaret, $v_tea_IT, $v_tea_MA), list($v_stu_SA, $v_stu_IT, $v_stu_MA), list($v_dep_SA, $v_dep_IT, $v_dep_MA), $v_regularTeachers) = $createVegasReturn;
        
        $stu1 = $v_stu_SA[0];
        $stu2 = $v_stu_SA[1];
        $stu3 = $v_stu_IT[0];
        $stu4 = $v_stu_MA[0];
        $stu5 = $v_stu_MA[1];
        
        // simulate friend ships
        $stu1->addGenericLink($stu2->id);
        $stu2->addGenericLink($stu3->id);
        $stu3->addGenericLink($stu1->id);
        $stu3->addGenericLink($stu2->id);
        
        // get friends stu3 has declared
        $sel = $api->selectVegas($t_student)
                   ->linkedTo($stu3,     
                             Mira_Core_Select_VegaSelect::LINK_TYPE_GENERIC, 
                             Mira_Core_Select_VegaSelect::LINK_DIRECTION_TO)
                   ->fetchAll();
        $this->assertSame(2, count($sel));
        
        // remove 1 friendship
        $stu3->deleteGenericLink($stu1->id);
        
        // get friends stu3 has declared
        $sel = $api->selectVegas($t_student)
                         ->linkedTo($stu3,     
                             Mira_Core_Select_VegaSelect::LINK_TYPE_GENERIC, 
                             Mira_Core_Select_VegaSelect::LINK_DIRECTION_TO)
                         ->fetchAll();
        $this->assertSame(1, count($sel));

        // get guys that declared stu3 as friend
        $sel = $api->selectVegas($t_student)
                         ->linkedTo($stu3,     
                             Mira_Core_Select_VegaSelect::LINK_TYPE_GENERIC, 
                             Mira_Core_Select_VegaSelect::LINK_DIRECTION_FROM)
                         ->fetchAll();
        $this->assertSame(1, count($sel));
        $s = $sel[0];
        $this->assertSame($stu2->id, $s->id);
    }
    
    // ###################################################
    // UTILS
    // ###################################################

    private function averageOfStudents($students)
    {
        $sum = 0;
        foreach ($students as $student) {
            $sum += $student->__get("average mark");    
        }
        return $sum/count($students);
    }
    
}