<?

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

class Test030Session extends Mira_Core_Test_TestCase
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
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
     */
	public function testInit()
	{
	    $sv = new Mira_Service_SessionService();
	    return $sv;
	}
    
    /**
     * @depends testInit
     * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
	 *
	 * @param SessionService $sv
     */
	public function testConnection($sv)
	{
	    $this->assertSame($sv->testConnection(), "success");
	}
    
    /**
     * @depends testInit
     * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
	 *
	 * @param SessionService $sv
     */
	public function testSuccessfulLogin($sv)
	{
	    /**
	     * @var Mira_Rpc_Response
	     */
	    $result = $sv->login($this->email, $this->pass);
	    $this->assertTrue($result instanceof Mira_Core_User);
	    $this->assertSame($result->email, $this->email);
	}
	
    /**
     * @depends testInit
     * 
     * @codereview_owner maz
     * @codereview_reviewer andres
     * @codereview_status accepted
	 *
	 * @param SessionService $sv
	 * 
	 * @expectedException Mira_Core_Exception_BadRequestException
     */
	public function testFailedLogin($sv)
	{
	    /**
	     * @var Mira_Rpc_Response
	     */
	    $result = $sv->login($this->email, "pass"); // fails BadRequestException
	}
}
