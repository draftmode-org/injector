<?php
namespace Terrazza\Component\Injector\Tests;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Terrazza\Component\Injector\Exception\InjectorException;
use Terrazza\Component\Injector\Injector;
use Terrazza\Component\Injector\InjectorInterface;
use Terrazza\Component\Injector\Tests\Application\Logger;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryA;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryAInterface;

class InjectorTest extends TestCase {
    protected function getLogger(int $logLevel=null) : LoggerInterface {
        return new Logger($logLevel);
    }
    protected function getInjector($classMapping, LoggerInterface $logger) : InjectorInterface {
        return (new Injector(
            $classMapping,
            $logger
        ));
    }

    function testNative() {
        echo PHP_EOL.__METHOD__.PHP_EOL;
        $logger         = $this->getLogger();
        $injector       = $this->getInjector(__DIR__ . "/Examples/Native/di.config.php", $logger);
        $bridge         = $injector->get(Examples\Native\InjectorBridge::class);
        echo "...runtime:".round($injector->getRuntime(), 3).PHP_EOL;
        echo "...count:".$injector->getContainerCacheCount().PHP_EOL;
        $payment        = $bridge->createPayment($amount = 12.3);
        $this->assertEquals($amount, $payment->getAmount());
    }

    function testCommandBus() {
        echo PHP_EOL.__METHOD__.PHP_EOL;
        $logger         = $this->getLogger();
        $injector       = $this->getInjector(__DIR__ . "/Examples/CommandBus/di.config.php", $logger);
        $bridge         = $injector->get(Examples\CommandBus\Bridge\PaymentBridge::class);
        echo "...runtime:".round($injector->getRuntime(), 3).PHP_EOL;
        echo "...count:".$injector->getContainerCacheCount().PHP_EOL;
        $payment        = $bridge->createPayment($amount = 12.3);
        $this->assertEquals($amount, $payment->getAmount());
    }

    function testMapArray() {
        $logger         = $this->getLogger();
        $injector       = $this->getInjector([
            InjectorRepositoryAInterface::class => InjectorRepositoryA::class
        ], $logger);
        $class          = $injector->get(InjectorRepositoryA::class);
        $this->assertEquals(InjectorRepositoryA::class, get_class($class));
    }

    /** ---------------------------------------- */
    /** -------------- EXCEPTIONS -------------- */
    /** ---------------------------------------- */
    function testExceptionClassMappingFileNotFound() {
        $this->expectException(InjectorException::class);
        (new Injector(
            __DIR__ . "/Examples/di.config.php",
            $this->getLogger()
        ))->get(Examples\Native\InjectorBridge::class);
    }

    function testExceptionClassMappingArrayInvalid() {
        $this->expectException(InjectorException::class);
        (new Injector(
            __DIR__ . "/Examples/di.invalid.php",
            $this->getLogger()
        ))->get(Examples\Native\InjectorBridge::class);
    }
}