<?php

use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryA;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryAInterface;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryB;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryBInterface;
use Terrazza\Component\Injector\Tests\Examples\Native\InjectorUseCaseA;
use Terrazza\Component\Injector\Tests\Examples\Native\InjectorUseCaseAInterface;
use Terrazza\Component\Injector\Tests\Examples\Native\InjectorUseCaseB;
use Terrazza\Component\Injector\Tests\Examples\Native\InjectorUseCaseBInterface;

return [
    InjectorUseCaseAInterface::class => InjectorUseCaseA::class,
    InjectorUseCaseBInterface::class => InjectorUseCaseB::class,

    InjectorRepositoryAInterface::class => InjectorRepositoryA::class,
    InjectorRepositoryBInterface::class => InjectorRepositoryB::class
];