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
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */

/**
 * @see Zend_Loader 
 */
require_once 'Zend/Loader.php';

/**
 * @category   Mira
 * @package    Mira_Utils
 * @copyright  Copyright (c) 2010 Vega (http://www.getvega.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
class Mira_Utils_Loader extends Zend_Loader
{

    public static function loadClass ($class, $dirs = null, $suppressWarnings = false)
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return;
        }
        if ((null !== $dirs) && ! is_string($dirs) && ! is_array($dirs)) {
            /**
             * @see Zend_Exception
             */
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('Directory argument must be a string or an array');
        }
        // Autodiscover the path from the class name
        // Implementation is PHP namespace-aware, and based on 
        // Framework Interop Group reference implementation:
        // http://groups.google.com/group/php-standards/web/psr-0-final-proposal
        $className = ltrim($class, '\\');
        $file = '';
        $namespace = '';
        if (($lastNsPos = strripos($className, '\\')) === true) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $file .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        if (! empty($dirs)) {
            // use the autodiscovered path
            $dirPath = dirname($file);
            if (is_string($dirs)) {
                $dirs = explode(PATH_SEPARATOR, $dirs);
            }
            foreach ($dirs as $key => $dir) {
                if ($dir == '.') {
                    $dirs[$key] = $dirPath;
                } else {
                    $dir = rtrim($dir, '\\/');
                    $dirs[$key] = $dir . DIRECTORY_SEPARATOR . $dirPath;
                }
            }
            $file = basename($file);
            self::loadFile($file, $dirs, true, $suppressWarnings);
        } else {
            self::loadFile($file, null, true, $suppressWarnings);
        }
        if (! class_exists($class, false) && ! interface_exists($class, false)) {
            /**
             * @see Zend_Exception 
             */
            require_once 'Zend/Exception.php';
            throw new Zend_Exception("File \"$file\" does not exist or class \"$class\" was not found in the file");
        }
    }

    public static function loadFile ($filename, $dirs = null, $once = false, $suppressWarnings = false)
    {
        self::_securityCheck($filename);
        /**
         * Search in provided directories, as well as include_path
         */
        $incPath = false;
        if (! empty($dirs) && (is_array($dirs) || is_string($dirs))) {
            if (is_array($dirs)) {
                $dirs = implode(PATH_SEPARATOR, $dirs);
            }
            $incPath = get_include_path();
            set_include_path($dirs . PATH_SEPARATOR . $incPath);
        }
        /**
         * Try finding for the plain filename in the include_path.
         */
        if ($once) {
            if (self::fileExistsIP($filename)) {
                include_once $filename;
            } elseif (! $suppressWarnings) {
                throw new Exception("Could not load file $filename");
            }
        } else {
            if (self::fileExistsIP($filename)) {
                include $filename;
            } elseif (! $suppressWarnings) {
                throw new Exception("Could not load file $filename");
            }
        }
        /**
         * If searching in directories, reset include_path
         */
        if ($incPath) {
            set_include_path($incPath);
        }
        return true;
    }

    public static function fileExistsIP ($filename)
    {
        if (function_exists("get_include_path")) {
            $include_path = get_include_path();
        } elseif (false !== ($ip = ini_get("include_path"))) {
            $include_path = $ip;
        } else {
            return false;
        }
        if (false !== strpos($include_path, PATH_SEPARATOR)) {
            if (false !== ($temp = explode(PATH_SEPARATOR, $include_path)) && count($temp) > 0) {
                for ($n = 0; $n < count($temp); $n ++) {
                    if (false !== @file_exists($temp[$n] . DIRECTORY_SEPARATOR . $filename)) {
                        return true;
                    }
                }
                return false;
            } else {
                return false;
            }
        } elseif (! empty($include_path)) {
            if (false !== @file_exists($include_path)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
?>