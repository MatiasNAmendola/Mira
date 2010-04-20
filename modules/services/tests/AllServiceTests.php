<?php

require_once dirname(__FILE__) . '/../../../resources/tests/includes.php';
require_once 'Mira/Utils/Test/TestSuite.php';

class AllServiceTests extends Mira_Utils_Test_TestSuite
{
    protected function addTests ()
    {
        $this->addTestFile(dirname(__FILE__) . "/../../core/tests/Test020Selects.php", true);
        $this->addTestFile(dirname(__FILE__) . "/../../core/tests/Test021SchoolSample.php", true);
        $this->addTestFolder(dirname(__FILE__), false);
    }    
    
    public function getName()
    {
        return "AllServiceTests";
    }
    
    public static function suite ()
    {
        return new self();
    }
}

