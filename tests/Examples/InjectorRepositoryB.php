<?php

namespace Terrazza\Component\Injector\Tests\Examples;

class InjectorRepositoryB implements InjectorRepositoryBInterface {
    public function __construct() {
        echo __METHOD__.PHP_EOL;
    }
    function handle() : void {
        echo __METHOD__.PHP_EOL;
    }
}