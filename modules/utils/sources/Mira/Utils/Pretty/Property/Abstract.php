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
 * @subpackage Pretty
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @category   Mira
 * @package    Mira_Utils
 * @subpackage Pretty
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
abstract class Mira_Utils_Pretty_Property_Abstract
{
	// @var object
    public $target;
	// @var string
    public $prettyName;
	// @var boolean
    public $useCache;
	// @var boolean
    public $autocommit;
	// @var boolean
    public $transient;
	// @var object
    private $_cachedValue;
	// @var boolean
    private $_isDirty;

    public function __construct ($target, $prettyName, $useCache = false, $autocommit = true, $transient = false)
    {
        if (!$autocommit && !$useCache)
            throw new Exception("useCache has to be set to true when autocommit is false");
        
        $this->_isDirty = false;
        $this->target = $target;
        $this->prettyName = $prettyName;
        $this->useCache = $useCache;
        $this->autocommit = $autocommit;
        $this->transient = $transient;
    }

    public function getValue ()
    {
        throw new Exception("getValue() function has to be overriden");
    }

    public function setValue ($value)
    {
        throw new Exception("setValue() function has to be overriden.");
    }

    public function isDirty ()
    {
        return $this->_isDirty;
    }
    
    public function valueEquals ($value)
    {
        $thisValue = $this->internalGetValue();
        return $thisValue == $value;
    }
    
    public function commit()
    {
        if ($this->useCache && $this->_isDirty) {
            $this->setValue($this->_cachedValue);
        }
    }

    public function internalGetValue ()
    {
        if ($this->useCache && isset($this->_cachedValue)) {
            return $this->_cachedValue;
        } else {
            $ret = $this->getValue();
            if ($this->useCache) {
                $this->_cachedValue = $ret;
            }
            return $ret;
        }
    }

    public function internalSetValue ($value)
    {
        // @todo investigate this goes into inifinite loop
//        if (!$this->valueEquals($value)) {
            $this->_isDirty = true; 
            if ($this->useCache)
                $this->_cachedValue = $value;
            if ($this->autocommit)
                $this->setValue($value);
//        }
    }
}
?>