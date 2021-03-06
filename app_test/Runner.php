<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

define("TEST_APP_PATH", "../app/");
define("CORE_PATH", TEST_APP_PATH . "core/");

require_once CORE_PATH . "ColorCli.php";

class Assert {
    public static function assertTrue($name, $value) {
        echo $value == true ? \Core\ColorCli::green("$name=true OK") : \Core\ColorCli::red("$name=true FAIL!"), PHP_EOL;
    }
    public static function assertNotFalse($name, $value) {
        echo $value != false ? \Core\ColorCli::green("$name!=false OK") : \Core\ColorCli::red("$name!=false FAIL!"), PHP_EOL;
    }
    public static function assertEquals($name, $expectedValue , $value) {
        echo $value == $expectedValue ? \Core\ColorCli::green("$name=$value OK") : \Core\ColorCli::red("$name=$value, expected=$expectedValue FAIL!"), PHP_EOL;
    }
    public static function assertNotNull($name, $value) {
        echo $value != null ? \Core\ColorCli::green("$name!=null OK") : ColorCli::red("$name!=null FAIL"), PHP_EOL;
    }
    public static function assertObjectNotNull($name, $value) {
        echo $value != null ? \Core\ColorCli::green("$name!=null (".get_class($value).") OK") : \Core\ColorCli::red("$name!=null FAIL"), PHP_EOL;
    }
}

class Runner {
    public function run() {
        echo PHP_EOL;
        echo \Core\ColorCli::cyan("  /\\"), PHP_EOL;
        echo \Core\ColorCli::cyan(" /  \\"), "  ", \Core\ColorCli::cyan("Cell6 Unit Test Runner"), PHP_EOL;
        echo \Core\ColorCli::cyan("/__  \\"), PHP_EOL;
        echo PHP_EOL;

        $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('test'));
        $testFiles = new RegexIterator($allFiles, '/\Test.php$/');

        foreach ($testFiles as $file) {
            require_once $file;
            $pathInfo = pathinfo($file);
            $className = $pathInfo['filename'];

            if (class_exists($className)) {
                $this->runMethods($className);
            }
        }

        echo PHP_EOL;
    }

    private function runMethods($className) {
        $class = new ReflectionClass($className);
        $instance = $class->newInstance();

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor()) {
                continue;
            }
            echo \Core\ColorCli::light_gray($className . "::" . $method->getName()), PHP_EOL;
            $method->invoke($instance);
        }
    }
}

$runner = new Runner();
$runner->run();