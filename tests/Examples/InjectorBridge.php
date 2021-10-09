<?php

namespace Terrazza\Component\Injector\Tests\Examples;

class InjectorBridge implements InjectorBridgeInterface {
    private InjectorUseCaseAInterface $useCaseA;
    private InjectorUseCaseBInterface $useCaseB;
    public function __construct(InjectorUseCaseAInterface $useCaseA, InjectorUseCaseBInterface $useCaseB) {
        $this->useCaseA = $useCaseA;
        $this->useCaseB = $useCaseB;
        var_dump(__NAMESPACE__."->__construct()");
    }

    function handleA() : void {
        var_dump(__NAMESPACE__."->handle()A");
        $this->useCaseA->handle();
    }

    function handleB() : void {
        var_dump(__NAMESPACE__."->handle()B");
        $this->useCaseB->handle();
    }
}