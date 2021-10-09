<?php

namespace Terrazza\Component\Injector\Tests\Examples;

class InjectorRepositoryB implements InjectorRepositoryBInterface {
    public function __construct() {
        var_dump(__NAMESPACE__."->__construct()");
    }
    function handle() : void {
        var_dump(__NAMESPACE__."->handle()");
    }
}