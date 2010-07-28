<?php

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Utils/Test/TestSuite.php';

class AllSelectTests extends Mira_Utils_Test_TestSuite
{
    protected function addTests ()
    {
        $this->addTestFile(dirname(__FILE__) . "/Test020Selects.php");
        $this->addTestFile(dirname(__FILE__) . "/Test021SchoolSample.php");
    }    
    
    public function getName()
    {
        return "AllSelectTests";
    }
    
    public static function suite ()
    {
        return new self();
    }
}

