<?php
namespace Terrazza\Component\Injector\Tests;
require_once("LoggerTest.php");
use PHPUnit\Framework\TestCase;
use Terrazza\Component\Injector\Exception\InjectorException;
use Terrazza\Component\Injector\Injector;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryA;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryAInterface;
use Terrazza\Component\Injector\Tests\Examples\Native\InjectorBridge;

class InjectorTest extends TestCase {

    function testNative() {
        echo PHP_EOL.__METHOD__.PHP_EOL;
        $logger = new LoggerTest();
        $logger->debug("");
        $injector = (new Injector(
            __DIR__ . "/Examples/Native/di.config.php",
            $logger
        ));
        $class = $injector->get(Examples\Native\InjectorBridge::class);
        $class->handleA();
        //
        $this->assertTrue($injector->has(InjectorBridge::class));
    }

    function testCommandBus() {
        echo PHP_EOL.__METHOD__.PHP_EOL;
        $logger                                     = new LoggerTest();
        $logger->debug("");
        //
        $class = (new Injector(
            __DIR__ . "/Examples/CommandBus/di.config.php",
            $logger
        ))->get(Examples\CommandBus\InjectorBridge::class);
        $class->getProduct();
        //
        $this->assertTrue(true);
    }

    function testMapArray() {
        $logger                                     = new LoggerTest();
        $class = (new Injector(
            [
                InjectorRepositoryAInterface::class => InjectorRepositoryA::class
            ],
            $logger
        ))->get(InjectorRepositoryA::class);
        $this->assertEquals(InjectorRepositoryA::class, get_class($class));
    }

    function testExceptionClassMappingFileNotFound() {
        $logger                                     = new LoggerTest();
        $this->expectException(InjectorException::class);
        $class = (new Injector(
            __DIR__ . "/Examples/di.config.php",
            $logger
        ))->get(Examples\CommandBus\InjectorBridge::class);
    }

    function testExceptionClassMappingArrayInvalid() {
        $logger                                     = new LoggerTest();
        $this->expectException(InjectorException::class);
        (new Injector(
            __DIR__ . "/Examples/di.invalid.php",
            $logger
        ))->get(Examples\CommandBus\InjectorBridge::class);
    }
}