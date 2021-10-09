<?php

namespace Terrazza\Component\Injector\Tests\Examples;

class InjectorUseCaseA implements InjectorUseCaseAInterface {
    private InjectorRepositoryAInterface $repository;
    public function __construct(InjectorRepositoryAInterface $repository) {
        $this->repository = $repository;
        var_dump(__NAMESPACE__."->__construct()");
    }

    function handle() : void {
        var_dump(__NAMESPACE__."->handle()");
        $this->repository->handle();
    }
}