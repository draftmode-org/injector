<?php
namespace Terrazza\Component\Injector\Tests\Examples\Native;

class InjectorBridge  {
    private InjectorUseCaseAInterface $useCaseA;
    private InjectorUseCaseBInterface $useCaseB;
    public function __construct(InjectorUseCaseAInterface $useCaseA, InjectorUseCaseBInterface $useCaseB) {
        $this->useCaseA = $useCaseA;
        $this->useCaseB = $useCaseB;
        echo __METHOD__.PHP_EOL;
    }

    function handleA() : void {
        echo __METHOD__." before ".PHP_EOL;
        $this->useCaseA->handle();
        echo __METHOD__." after ".PHP_EOL;
    }

    function handleB() : void {
        echo __METHOD__." before ".PHP_EOL;
        $this->useCaseB->handle();
        echo __METHOD__." after ".PHP_EOL;
    }
}