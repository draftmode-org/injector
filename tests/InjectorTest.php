<?php
namespace Terrazza\Component\Injector\Tests;
use PHPUnit\Framework\TestCase;
use Terrazza\Component\Injector\Injector;
use Terrazza\Component\Injector\Tests\Examples\InjectorBridge;

class InjectorTest extends TestCase {
    function testCommon() {
        $class = (new Injector(
            __DIR__ . DIRECTORY_SEPARATOR . "Examples" . DIRECTORY_SEPARATOR . "di.config.php"
        ))->get(InjectorBridge::class);
        $class->handleA();
        $this->assertTrue(true);
    }
}