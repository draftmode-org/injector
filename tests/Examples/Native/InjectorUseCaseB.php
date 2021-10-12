<?php

namespace Terrazza\Component\Injector\Tests\Examples\Native;

use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryBInterface;

class InjectorUseCaseB implements InjectorUseCaseBInterface {
    private InjectorRepositoryBInterface $repository;
    public function __construct(InjectorRepositoryBInterface $repository) {
        $this->repository = $repository;
        echo __METHOD__.PHP_EOL;
    }

    function handle() : void {
        echo __METHOD__.PHP_EOL;
        $this->repository->handle();
    }
}