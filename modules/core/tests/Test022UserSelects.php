<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

class Test022UserSelects extends Mira_Core_Test_TestCase
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
     * @return Mira
     * 
     * @codereview_owner andres
     * @codereview_reviewer maz
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
     * @return Mira_Core_User
     * 
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
    public function testFindUser($api)
    {
        $user = $api->createUser("juanesteban@hotmail.com", "somethingPass");
        $firstName = "first name";
	    $lastName = "last name";
	    $user->contact->phone = "324";
	    $user->contact->address = "Medellin";
	    $user->contact->$firstName = "Juan";
	    $user->contact->$lastName = "Esteban";
	    $user->save();
	    $user->account = "validated";
	    $user->save();
	    
        return $user;
    }

    /**
     * @depends testApiCreation
     * @depends testFindUser
     * 
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
    public function testFindById($api, $user)
    {
        $select = $api->selectUsers();
        $select->where("id", $user->id);
        $result = $select->fetchObject();
        
        $this->assertSame($result->id, $user->id);
        $this->assertSame($result->name, $user->name);
    }
    
    /**
     * @depends testApiCreation
     * @depends testFindUser
     * 
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
    public function testFindByEmail($api, $user)
    {
        $select = $api->selectUsers();
        $select->where("email", $user->email);
        $result = $select->fetchObject();
        
        $this->assertSame($result->id, $user->id);
        $this->assertSame($result->email, $user->email);
    }
    
    /**
     * @depends testApiCreation
     * @depends testFindUser
     * 
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
    public function testFindByToken($api, $user)
    {
        $select = $api->selectUsers();
        $select->where("token", $user->token);
        $result = $select->fetchObject();
        
        $this->assertSame($result->id, $user->id);
        $this->assertSame($result->token, $user->token);
    }
    
    /**
     * @depends testApiCreation
     * @depends testFindUser
     * 
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
    public function testFindIntelli($api, $user)
    {
        $query = substr($user->contact->email, 4, -11);  // returns "este" 
        
        $select = $api->selectUsers();
        $select->where("email", $query, "permissive");
        $result = $select->fetchObject();
        
        $this->assertSame($result->id, $user->id);
        $this->assertSame($result->email, $user->email);
    }
    
    /**
     * @depends testApiCreation
     * 
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
    public function testFindByAccount($api)
    {
        $select = $api->selectUsers();
        $select->where("account", "validated");
        $result = $select->fetchAll();
        
        $this->assertNotEquals(count($result), 0);
    }
    
    /**
     * @depends testApiCreation
     * @depends testFindUser
     * 
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
    public function testAllUserProperties($api, $user)
    {
        $select = $api->selectUsers();
        $select->where("email", $user->email);
        $select->where("id", $user->id);
        $select->where("token", $user->token);
        $select->where("account", "validated");
        $result = $select->fetchObject();
        
        $this->assertSame($result->id, $user->id);
        $this->assertSame($result->email, $user->email);
        $this->assertSame($result->token, $user->token);
    }
    
    /**
     * @depends testApiCreation
     * @depends testFindUser
     * 
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
    public function testFindByProperty($api, $user)
    {
        $firstName = "first name";
        $lastName = "last name";
        
        $api->login("juanesteban@hotmail.com", "somethingPass");
        
        $this->assertSame($user->contact->$firstName, "Juan");
        $this->assertSame($user->contact->$lastName, "Esteban");
        
        $select = $api->selectUsers();
        $select->where("first name", $user->contact->$firstName);
        $select->where("last name", $user->contact->$lastName);
        $result = $select->fetchObject();
        
        $this->assertEquals($result->id, $user->id);
        $this->assertEquals($result->email, $user->email);
        $this->assertEquals($result->contact->$firstName, $user->contact->$firstName);
        $this->assertEquals($result->contact->$lastName, $user->contact->$lastName);
    }
    
}