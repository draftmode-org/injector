<?php
namespace Terrazza\Component\Injector\Tests\Examples\Native;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryAInterface;

class InjectorUseCaseA implements InjectorUseCaseAInterface {
    private InjectorRepositoryAInterface $repository;
    public function __construct(InjectorRepositoryAInterface $repository) {
        $this->repository = $repository;
        echo __METHOD__.PHP_EOL;
    }

    function handle() : void {
        echo __METHOD__.PHP_EOL;
        $this->repository->handle();
    }
}