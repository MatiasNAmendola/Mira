<?php

/**
 * Mira
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@gevega.com so we can send you a copy immediately.
 *
 * @category   Mira
 * @package    Mira_Utils
 * @subpackage Test
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @see Zend_Reflextion_Class 
 */
require_once 'Zend/Reflection/Class.php';
/**
 * @see Mira_Utils_Test_Annotation
 */
require_once 'Mira/Utils/Test/Annotation.php';


/**
 * @category   Mira
 * @package    Mira_Utils
 * @subpackage Test
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
abstract class Mira_Utils_Test_TestSuite extends PHPUnit_Framework_TestSuite
{

    public $codeReviews;
    
    public $allFiles;
    
    public $outputBuffer = "";
    
    public function __construct ()
    {
        $this->codeReviews = array();
        $this->allFiles = array();
        $this->addTests();
        $this->parseFiles();
        $this->generateCodeReviewStats();
    }
    
    abstract protected function addTests();
    
    protected function parseFiles()
    {
        usort($this->allFiles, array($this,"cmpPaths"));
        
        foreach ($this->allFiles as $file) {
            $this->doAddTestFile($file);
        }
    }
    
    /**
     * @access private
     */
    private function cmpPaths($a, $b) {
        $filenameA = substr(strrchr($a, DIRECTORY_SEPARATOR), 1);
        $filenameB = substr(strrchr($b, DIRECTORY_SEPARATOR), 1);
        $ret = strcmp($filenameA, $filenameB);
        return $ret;
    }
    
    public function addTestFolder($folderPath, $recursive = false) 
    {
        $dir = opendir($folderPath);
        $files = array();
        // @todo how to remove this warning ??
        while ($files[] = readdir($dir));
        closedir($dir);
        
        if (count($files) > 0) {
            foreach ($files as $file) {
                $fullpath = $folderPath . DIRECTORY_SEPARATOR . $file;
                if ($file && $file != ".svn" && $file != "." && $file != ".." 
                          && file_exists($fullpath) 
                          && !(strpos($file, "TestSuite") === 0)
                          && !(strpos($file, "All") === 0)
                          && !(strpos($file, "Suite") === 0)) {
                    if (is_file($fullpath)) {
                        $this->addTestFile($fullpath);
                    } elseif ($recursive) {
                        $this->addTestFolder($fullpath, $recursive);
                    }
                }
            }
        }
    }
    
    private $_lastTest;
    
    /**
     * @param  PHPUnit_Framework_Test $test
     * @param  array                  $groups
     */
    public function addTest(PHPUnit_Framework_Test $test, $groups = array())
    {
        parent::addTest($test, $groups);
        $this->_lastTest = $test;
    }
     /**
     * @param  string  $filename
     * @param  boolean $syntaxCheck
     * @param  array   $phptOptions Array with ini settings for the php instance
     *                              run, key being the name if the setting,
     *                              value the ini value.
     * @throws InvalidArgumentException
     * @since  Method available since Release 2.3.0
     * @author Stefano F. Rausch <stefano@rausch-e.net>
     */
    public function addTestFile($path, $syntaxCheck = TRUE, $phptOptions = array())
    {
        $this->allFiles[] = $path;
    }
    
    public function doAddTestFile($path)
    {
        parent::addTestFile($path);

        if (!$this->_lastTest) return;
        
        $classname = $this->_lastTest->getName();
//        $this->printMsg ("\Adding test case : $classname\n");
        
        $reflectedTest = new Zend_Reflection_Class ($classname);
        $methods = $reflectedTest->getMethods(ReflectionMethod::IS_PUBLIC);
        
        foreach ($methods as $method) {
            
            $methodName = $method->getName();
            
            if (strncmp($methodName, "test", strlen("test")) == 0) {
                try {
                    $docblock = $method->getDocblock();
                	$tags = $docblock ? $docblock->getTags() : null;
                	$cr = $this->generateCodeReviewObject($tags, $classname, $methodName);
                    $this->codeReviews[] = $cr;
                } catch (Zend_Reflection_Exception $e) {
                	$cr = $this->generateCodeReviewObject(null, $classname, $methodName);
                    $this->codeReviews[] = $cr;
                }
            }
        }
        
        $this->_lastTest = null;
    }
    
    public function generateCodeReviewStats()
    {
        $cnt = count($this->codeReviews);
        if ($cnt == 0) {
            $this->printMsg("No test / code review found");   
            return; 
        }
        
        $badlyDocumented = array();
        $none = array();
        $started = array();
        $finished = array();
        $rejected = array();
        $accepted = array();
        
        foreach ($this->codeReviews as $cr) {
            switch ($cr->status) {
                case Mira_Utils_Test_Annotation::STATUS_BADLYDOCUMENTED: $badlyDocumented[] = $cr; break;
                case Mira_Utils_Test_Annotation::STATUS_NONE: $none[] = $cr; break;
                case Mira_Utils_Test_Annotation::STATUS_STARTED: $started[] = $cr; break;
                case Mira_Utils_Test_Annotation::STATUS_FINISHED: $finished[] = $cr; break;
                case Mira_Utils_Test_Annotation::STATUS_REJECTED: $rejected[] = $cr; break;
                case Mira_Utils_Test_Annotation::STATUS_ACCEPTED: $accepted[] = $cr; break;
            }
        }
        
        $this->printMsg ("\n\n" .$this->getName() . " TestsSuite CodeReview status :\n");
        $this->printMsg ("===================================================================\n");
        $this->printMsg (count($accepted) . " tests accepted out of " . count($this->codeReviews) . " (" . (100*count($accepted)/count($this->codeReviews)) ."%) \n");
        
        $this->printMsg ("\n-------- BADLY DOCUMENTED : " . (count($badlyDocumented) > 0 ? "/!\\ " . count($badlyDocumented) : "") . "\n");
        foreach ($badlyDocumented as $cr) {
            $this->printMsg (". $cr \n");
        }
        
        $this->printMsg ("\n-------- NOT STARTED (status = none) : " . count($none) . "\n");
        foreach ($none as $cr) {
            $this->printMsg (". $cr \n");
        }
        
        $this->printMsg ("\n-------- STARTED : " . count($started) . "\n");
        
        foreach ($started as $cr) {
            $this->printMsg (". $cr \n");
        }
        
        $this->printMsg ("\n-------- TO BE REVIEWED (status = finished) : " . count($finished) . "\n");
        
        foreach ($finished as $cr) {
            $this->printMsg (". $cr \n");
        }
        
        $this->printMsg ("\n-------- TO BE RESTARTED (status = rejected) : " . (count($rejected) > 0 ? "/!\\ " . count($rejected) : "") . "\n");
        
        foreach ($rejected as $cr) {
            $this->printMsg (". $cr \n");
        }
        
        $this->printMsg ("\n-------- ACCEPTED : " . count($accepted) . "\n");
        
        foreach ($accepted as $cr) {
            $this->printMsg (". $cr \n");
        }
    }
    
    public static function generateCodeReviewObject($tags, $testName, $testMethodName)
    {
        $ret = new Mira_Utils_Test_Annotation();
        $ret->testName = $testName;
        $ret->testMethodName = $testMethodName;
        $ret->status = Mira_Utils_Test_Annotation::STATUS_BADLYDOCUMENTED;
        
        if ($tags) {
            foreach ($tags as $tag) {
                switch ($tag->getName()) {
                    case "codereview_owner" : $ret->owner = $tag->getDescription(); break;
                    case "codereview_reviewer" : $ret->reviewer = $tag->getDescription(); break;
                    case "codereview_status" : 
                        
                        $tagDesc = $tag->getDescription();
                        if (!$tagDesc || $tagDesc == "") $tagDesc = Mira_Utils_Test_Annotation::STATUS_NONE;
                        
                        if ($tagDesc == Mira_Utils_Test_Annotation::STATUS_NONE
                            || $tagDesc == Mira_Utils_Test_Annotation::STATUS_STARTED
                            || $tagDesc == Mira_Utils_Test_Annotation::STATUS_FINISHED
                            || $tagDesc == Mira_Utils_Test_Annotation::STATUS_REJECTED
                            || $tagDesc == Mira_Utils_Test_Annotation::STATUS_ACCEPTED) {
                            
                            $ret->status = $tagDesc;
                        
                        } else {

                            $this->printMsg("\t /!\\ Wrong status specified for $testName:$testMethodName.\n" .
                                      	"\t=> Status has to be either \n" .
                            			"\t\t'none'\n" .
                            			"\t\t'started'\n" .
                            			"\t\t'finished'\n" .
                            			"\t\t'rejected'\n" .
                            			"\t\t'accepted'\n");
                            
                        }
                        break;
                    default: break;
                }
            }
        }
        
        return $ret;
    }
    
    /**
     * @access private
     */
    private function printMsg($msg)
    {
        $this->outputBuffer .= $msg; 
    } 
    
    protected function tearDown()
    {
        print($this->outputBuffer);
    }
}

