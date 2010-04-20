<?php

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Utils/Test/TestSuite.php';

class AllCoreTests extends Mira_Utils_Test_TestSuite
{
    protected function addTests ()
    {
        $this->addTestFolder(dirname(__FILE__), false);
    }    
    
    public function getName()
    {
        return "AllCoreTests";
    }
    
    public static function suite ()
    {
        return new self();
    }
}

