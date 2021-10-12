<?php

use Terrazza\Component\Injector\ActionHandler\DefaultActionHandler;
use Terrazza\Component\Injector\ActionHandlerInterface;
use Terrazza\Component\Injector\Tests\Examples\CommandBus\ProductGetAction;
use Terrazza\Component\Injector\Tests\Examples\CommandBus\ProductGetActionHandler;
use Terrazza\Component\Injector\Tests\Examples\CommandBus\ProductSaveAction;
use Terrazza\Component\Injector\Tests\Examples\CommandBus\ProductSaveActionHandler;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryA;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryAInterface;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryB;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryBInterface;

return [
    ActionHandlerInterface::class => DefaultActionHandler::class,
    DefaultActionHandler::class => [
        "actionMapper" => [
            ProductGetAction::class => ProductGetActionHandler::class,
            ProductSaveAction::class => ProductSaveActionHandler::class
        ]
    ],
    InjectorRepositoryAInterface::class => InjectorRepositoryA::class,
    InjectorRepositoryBInterface::class => InjectorRepositoryB::class,
];