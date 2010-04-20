<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

class Test031User extends Mira_Core_Test_TestCase
{
	
	public static function setUpBeforeClass()
    {
        self::$sqldump = dirname(__FILE__) . '/../../../resources/tests/dump.sql';
        self::$config = dirname(__FILE__) . '/../../../resources/tests/config.ini';
        parent::setUpBeforeClass();
    }
    
    const CRED_EMAIL = "mazzz.spam@gmail.com";
    const CRED_PASS = "pass";
    
	public $email = "ayn_nian@hotmail.com";
	public $pass = "andres";
    
    /**
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
     */
    public function testCreateApi()
    {
        return Zend_Registry::get(Mira_Core_Constants::REG_API);
    }
    
    /**
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
     */
	public function testInit()
	{
	    $sv = new Mira_Service_UserService();
	    return $sv;
	}
	
	/**
	 * @depends testInit
	 * @depends testCreateApi
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
	 * 
	 * @param UserService $sv
	 */
	public function testCreateUser($sv, $api)
	{
	    $contactProperties = array(
	        "first name" => "Mathieu",
	        "last name" =>  "Lemaire",
	        "phone" => "0664543537"  
	    );
	    
        $user = $sv->createUser(self::CRED_EMAIL, self::CRED_PASS, $contactProperties); 
        $this->assertNotNull($user);
        
        $actual = $api->uemail(self::CRED_EMAIL);
        $this->assertNotNull($actual);
        $this->assertSame($user->email, $actual->email);
        $this->assertSame("Mathieu", $actual->contact->__get("first name"));
        $this->assertSame("Lemaire", $actual->contact->__get("last name"));
        $this->assertSame("0664543537", $actual->contact->phone);
	}
    
	/**
	 * @depends testInit
	 * @depends testCreateApi
	 * @depends testCreateUser
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
	 * 
	 * @param UserService $sv
	 * @param Mira $api
	 */
    public function testSendValidationEmail($sv, $api)
    {
        $sv->sendValidationEmail(self::CRED_EMAIL);
    }
    
	/**
	 * @depends testInit
	 * @depends testCreateApi
	 * @depends testCreateUser
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
	 * 
	 * @param UserService $sv
	 * @param Mira $api
	 * @expectedException Mira_Core_Exception_BadRequestException
	 */
	public function testCreateAlreadyExisting($sv, $api)
	{
        $user = $sv->createUser(self::CRED_EMAIL, self::CRED_PASS); 
	}
	
	/**
	 * @depends testInit
	 * @depends testCreateApi
	 * @depends testCreateUser
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
	 * 
	 * @param UserService $sv
	 * @param Mira $api
	 */
	public function testUpdateUser($sv, $api)
	{
	    $user = $sv->updateUser(self::CRED_EMAIL, array("phone" => "33"));
	    $actual = $api->uemail(self::CRED_EMAIL);
	    
	    $this->assertNotNull($actual);
	    $this->assertSame($actual->contact->__get("first name"), "Mathieu");
	    $this->assertSame($actual->contact->phone, "33");
	}

	/**
	 * @depends testInit
	 * @depends testCreateApi
	 * @depends testCreateUser
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
	 * 
	 * @param UserService $sv
	 * @param Mira $api
	 */
    public function testFind($sv, $api)
    {
        $filters = array("email" => self::CRED_EMAIL);
        $options = array("lazy" => true); 
        
        $results = $sv->find($filters, $options);
        
        $this->assertSame(count($results), 1);
        $this->assertSame(get_class($results[0]), "Mira_Core_Reference");
    }

	/**
	 * @depends testInit
	 * @depends testCreateApi
	 * @depends testCreateUser
	 * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
	 * 
	 * @param UserService $sv
	 * @param Mira $api
	 * 
	 * @expectedException Mira_Core_Exception_NotFoundException
	 */
    public function testNotFound($sv, $api)
    {
        $sv->sendRecoverPasswordEmail("mazzzzzzz@gmail.com");
    }
}
