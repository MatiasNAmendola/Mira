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
 * @category   Mira
 * @package    Mira_Utils
 * @subpackage Test
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Utils_Test_Annotation
{
    const STATUS_BADLYDOCUMENTED = 'badly documented';
    
    const STATUS_NONE = 'none';
    const STATUS_STARTED = 'started';
    const STATUS_FINISHED = 'finished';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ACCEPTED = 'accepted';
    
    /**
     * @var string
     */
    public $owner;
    /**
     * @var string
     */
    public $reviewer;
    /**
     * @var string
     */
    public $status;
    /**
     * @var string
     */
    public $testName;
    /**
     * @var string
     */
    public $testMethodName;
    
    public function __toString()
    {
        return "$this->testName::$this->testMethodName [$this->status] - $this->owner " . ($this->reviewer ? " reviewed by " . $this->reviewer : "");
    }
}
?>