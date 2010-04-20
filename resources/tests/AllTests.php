<?php

require_once dirname(__FILE__) . '/includes.php';
require_once 'Mira/Utils/Test/TestSuite.php';

class AllTests extends Mira_Utils_Test_TestSuite
{
    protected function addTests ()
    {
        $this->addTestFile(dirname(__FILE__) . "/../../modules/core/tests/AllCoreTests.php", true);
        $this->addTestFile(dirname(__FILE__) . "/../../modules/services/tests/AllServiceTests.php", true);
    }    
    
    public function getName()
    {
        return "AllTests";
    }
    
    public static function suite ()
    {
        return new self();
    }
}

