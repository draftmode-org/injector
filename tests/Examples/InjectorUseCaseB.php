<?php

namespace Terrazza\Component\Injector\Tests\Examples;

class InjectorUseCaseB implements InjectorUseCaseBInterface {
    private InjectorRepositoryBInterface $repository;
    public function __construct(InjectorRepositoryBInterface $repository) {
        $this->repository = $repository;
        var_dump(__NAMESPACE__."->__construct()");
    }

    function handle() : void {
        var_dump(__NAMESPACE__."->handle()");
        $this->repository->handle();
    }
}