<?php

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Core/Test/TestCase.php';

class Test001User extends Mira_Core_Test_TestCase
{
	public $email = "ayn_nian@hotmail.com";
	public $pass = "andres";
	
	public static function setUpBeforeClass()
    {
        self::$config = dirname(__FILE__) . '/../../../resources/tests/config.ini';
        parent::setUpBeforeClass();
    }
	
    /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testLogin()
	{
	    $api = new Mira("application");
	    $api->login($this->email, $this->pass);
        $user = $api->getUser();
        	    
		// check that the user has been authenticated
		$this->assertNotNull($user, "Problem with $this->email authentication");
		// check user properties
		$this->assertSame("ayn_nian@hotmail.com", $user->email);
		$contact = $user->contact;
		$this->assertNotNull($contact);
		$this->assertSame("Andres PENA",          $contact->name);
		$this->assertSame("2010-01-08 12:27:27",  $contact->creationDate);
		$this->assertSame("enabled",              $contact->status);
		$this->assertEquals(1,                    $contact->revision);
		$this->assertSame("Contact",              $contact->type->name);
		$this->assertSame("andres",               $contact->__get("first name"));
	}
	
    /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testCreateUser()
	{
	    //creating user
	    $api = new Mira("application");
	    $user = $api->createUser("andrejohanna@hotmail.com", "passTest");
	    $user->save();
	    
	    //finding user
	    $user = $api->uemail("andrejohanna@hotmail.com");
	    $this->assertSame("validating", $user->account);
	    $this->assertNotNull($user);
	    $this->assertNotNull($user->contact);
	}
	
    /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testEditUser()
	{
	    //finding user
	    $api = new Mira("application");
	    $user = $api->uemail("andrejohanna@hotmail.com");
	    $this->assertNotNull($user);
	    
	    //editing user
	    $firstName = "first name";
	    $lastName = "last name";
	    $user->contact->phone = "2687147";
	    $user->contact->address = "Jardin";
	    $user->contact->$firstName = "Andrea";
	    $user->contact->$lastName = "Franco";
	    $user->save();

	    $user = $api->uemail("andrejohanna@hotmail.com");
	    $this->assertSame($user->email, "andrejohanna@hotmail.com");
	    $this->assertSame($user->contact->phone, "2687147");
	    $this->assertSame($user->contact->address, "Jardin");
	    $this->assertSame($user->contact->$firstName, "Andrea");
	    $this->assertSame($user->contact->$lastName, "Franco");
	}
	
    /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testRetrievalUser()
	{
	    $firstName = "first name";
	    $lastName = "last name";
	    
	    $api = new Mira("application");
	    //finding user by email
	    $user = $api->uemail("andrejohanna@hotmail.com");
	    $this->assertNotNull($user);
	    $this->assertSame($user->email, "andrejohanna@hotmail.com");
	    $this->assertSame($user->contact->phone, "2687147");
	    $this->assertSame($user->contact->address, "Jardin");
	    $this->assertSame($user->contact->$firstName, "Andrea");
	    $this->assertSame($user->contact->$lastName, "Franco");
	    
	    $token = $user->token;

	    //finding user by token
	    $user = $api->selectUsers()->where("token", $token)
	            ->fetchObject();
	    $this->assertNotNull($user);
	    $this->assertSame($user->email, "andrejohanna@hotmail.com");
	    
	    $id = $user->id;
	    
	    //finding user by id
	    $user = $api->uid($id);
	    $this->assertNotNull($user);
	    $this->assertSame($user->email, "andrejohanna@hotmail.com");
	    
	    //finding all users
	    $users = $api->selectUsers()->fetchAll();
	    $this->assertNotNull($users);
	    
	    //finding all users by intelli 
	    $users = $api->selectUsers()->where("name", "johanna", "permissive")
	                 ->fetchAll();
	    $this->assertNotNull($users);
	    
	}
	
    /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testBadLogin()
	{
	    $api = new Mira("application");
	    $user = $api->uemail("andrejohanna@hotmail.com");
	    $user->save();
	    
	    $api->login("andrejohanna@hotmail.com", "passTest");
	    $userLogged = $api->getUser();
	    $this->assertSame("validating", $userLogged->account);
	    
	    // we can't do that here since there are no Zend Controller in place
	    // this test will be done in seperate project
        // $this->dispatch("account/validate/code/" . $user->token . "/email/" . $user->email);
	    // $user = $api->uid($user->id);
	    $userLogged->account = "validated";
	    $userLogged->save();
	    
	    $this->assertSame("validated", $userLogged->account);
	    
	    $api->login("andrejohanna@hotmail.com", "passTest");
	    $userLogged = $api->getUser(); 
	    $this->assertNotNull($userLogged);
	}
	
    /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testPasswordRecovery()
	{
	    $api = new Mira("application");
	    $api->login("andrejohanna@hotmail.com", "passTest");
	    $user = $api->getUser();
	    $this->assertNotNull($user);
	    
	    $api->logout();
	    
	    $user = $api->uemail("andrejohanna@hotmail.com");
	    $api->sendRecoverPasswordEmail($user);

        // same as testBadLogin
        // $this->dispatch("account/recoverpassword/code/" . $user->token . "/email/" . $user->email);
        $user->password = "otherPass";
        $user->save();
        
	    $api->login("andrejohanna@hotmail.com", "otherPass");
	    $userLogged = $api->getUser();
	    $this->assertNotNull($userLogged);
	    
	    $firstName = "first name";
        $lastName = "last name";
	    $this->assertSame($userLogged->contact->$firstName, "Andrea");
        $this->assertSame($userLogged->contact->$lastName, "Franco");
        
	}
	
    /**
     * @codereview_owner andres
     * @codereview_reviewer maz
     * @codereview_status accepted
     */
	public function testUserSelect(){
	    $firstName = "first name";
        $lastName = "last name";
	    
	    $api = new Mira("application");
        $api->login("andrejohanna@hotmail.com", "otherPass");
        $userLogged = $api->getUser(); 
        
	    $select = $api->selectUsers();
        $select->where("first name", $userLogged->contact->__get("first name"));
        $select->where("last name", $userLogged->contact->__get("last name"));
        $result = $select->fetchObject();
        $this->assertNotNull($result);
        $this->assertEquals($result->id, $userLogged->id);
        $this->assertEquals($result->email, $userLogged->email);
	}
}
