<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: Skeleton.php 539 2006-02-13 16:08:42Z sb $
//

/**
 * Class for creating a PHPUnit2_Framework_TestCase skeleton file.
 *
 * This class will take a classname as a parameter on construction and will
 * create a PHP file that contains the skeleton of a PHPUnit2_Framework_TestCase
 * subclass.
 *
 * <code>
 * <?php
 * require_once 'PHPUnit2/Util/Skeleton.php';
 *
 * $skeleton = new PHPUnit2_Util_Skeleton(
 *   'PHPUnit2_Util_Skeleton',
 *   'PHPUnit2/Util/Skeleton.php'
 * );
 *
 * $skeleton->write();
 * ?>
 * </code>
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Util
 * @since       2.1.0
 * @abstract
 */
class PHPUnit2_Util_Skeleton {
    // {{{ Instance Variables

    protected $templateClassHeader =
'<?php
if (!defined("PHPUnit2_MAIN_METHOD")) {
    define("PHPUnit2_MAIN_METHOD", "{className}Test::main");
}

require_once "PHPUnit2/Framework/IncompleteTestError.php";
require_once "PHPUnit2/Framework/TestCase.php";
require_once "PHPUnit2/Framework/TestSuite.php";

require_once "{classFile}";

/**
 * Test class for {className}.
 * Generated by PHPUnit2_Util_Skeleton on {date} at {time}.
 */
class {className}Test extends PHPUnit2_Framework_TestCase {
    public static function main() {
        require_once "PHPUnit2/TextUI/TestRunner.php";

        $suite  = new PHPUnit2_Framework_TestSuite("{className}Test");
        $result = PHPUnit2_TextUI_TestRunner::run($suite);
    }
';

    protected $templateClassFooter =
'}

if (PHPUnit2_MAIN_METHOD == "{className}Test::main") {
    {className}Test::main();
}
?>
';

    protected $templateMethod =
'
    /**
    * @todo Implement test{methodName}().
    */
    public function test{methodName}() {
        throw new PHPUnit2_Framework_IncompleteTestError;
    }
';

    /**
    * @var    string
    * @access protected
    */
    protected $className;

    /**
    * @var    string
    * @access protected
    */
    protected $classSourceFile;

    // }}}
    // {{{ public function __construct($className, $classSourceFile = '')

    /**
    * Constructor.
    *
    * @param  string  $className
    * @param  string  $classSourceFile
    * @access public
    */
    public function __construct($className, $classSourceFile = '') {
        if ($classSourceFile == '') {
            $classSourceFile = $className . '.php';
        }

        if (file_exists($classSourceFile)) {
            $this->classSourceFile = $classSourceFile;
        } else {
            throw new Exception(
              sprintf(
                'Could not open %s.',

                $classSourceFile
              )
            );
        }

        @include_once $this->classSourceFile;

        if (class_exists($className)) {
            $this->className = $className;
        } else {
            throw new Exception(
              sprintf(
                'Could not find class "%s" in %s.',

                $className,
                $classSourceFile
              )
            );
        }
    }

    // }}}
    // {{{ public function generate()

    /**
    * Generates the test class' source.
    *
    * @return string
    * @access public
    */
    public function generate() {
        $testClassSource = $this->testClassHeader($this->className, $this->classSourceFile);

        $class = new ReflectionClass($this->className);

        foreach ($class->getMethods() as $method) {
            if (!$method->isConstructor() &&
                !$method->isAbstract() &&
                 $method->isUserDefined() &&
                 $method->isPublic() &&
                 $method->getDeclaringClass()->getName() == $this->className) {
                $testClassSource .= $this->testMethod($method->getName());
            }
        }

        $testClassSource .= $this->testClassFooter($this->className);

        return $testClassSource;
    }

    // }}}
    // {{{ public function write()

    /**
    * Generates the test class and writes it to a source file.
    *
    * @param  string  $file
    * @access public
    */
    public function write($file = '') {
        if ($file == '') {
            $file = $this->className . 'Test.php';
        }

        if ($fp = @fopen($file, 'w')) {
            @fputs($fp, $this->generate());
            @fclose($fp);
        }
    }

    // }}}
    // {{{ public function setTemplates($classHeader, $classFooter, $method)

    /**
    * Sets the templates for class header, class footer, and method.
    *
    * @param  string  $classHeader
    * @param  string  $classFooter
    * @param  string  $method
    * @access public
    * @since  2.2.0
    */
    public function setTemplates($classHeader, $classFooter, $method) {
        if (is_file($classHeader)) {
            $this->templateClassHeader = file_get_contents($classHeader);
        } else {
            $this->templateClassHeader = $classHeader;
        }

        if (is_file($classFooter)) {
            $this->templateClassFooter = file_get_contents($classFooter);
        } else {
            $this->templateClassFooter = $classFooter;
        }

        if (is_file($method)) {
            $this->templateMethod = file_get_contents($method);
        } else {
            $this->templateMethod = $method;
        }
    }

    // }}}
    // {{{ protected function testClassHeader($className, $classSourceFile)

    /**
    * @param  string  $className
    * @param  string  $classSourceFile
    * @access protected
    */
    protected function testClassHeader($className, $classSourceFile) {
        return str_replace(
          array(
            '{className}',
            '{classFile}',
            '{date}',
            '{time}'
          ),
          array(
            $className,
            $classSourceFile,
            date('Y-m-d'),
            date('H:i:s')
          ),
          $this->templateClassHeader
        );
    }

    // }}}
    // {{{ protected function testClassFooter($className)

    /**
    * @param  string  $className
    * @access protected
    */
    protected function testClassFooter($className) {
        return str_replace(
          array(
            '{className}'
          ),
          array(
            $className
          ),
          $this->templateClassFooter
        );
    }

    // }}}
    // {{{ protected function testMethod($methodName)

    /**
    * @param  string  $methodName
    * @access protected
    */
    protected function testMethod($methodName) {
        return str_replace(
          array(
            '{methodName}'
          ),
          array(
            ucfirst($methodName)
          ),
          $this->templateMethod
        );
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
