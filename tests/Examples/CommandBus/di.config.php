<?php

use Psr\Log\LoggerInterface;
use Terrazza\Component\Injector\ActionHandler\DefaultActionHandler;
use Terrazza\Component\Injector\ActionHandlerBuilderInterface;
use Terrazza\Component\Injector\Tests\Examples\CommandBus\Action\Payment\PaymentCreateActionHandler;
use Terrazza\Component\Injector\Tests\Examples\CommandBus\Action\PaymentActionHandler;
use Terrazza\Component\Injector\Tests\Examples\CommandBus\Bridge\Payment\PaymentCreateAction;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryA;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryAInterface;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryB;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryBInterface;
use Terrazza\Component\Injector\Tests\Logger;

return [
    //
    LoggerInterface::class                  => Logger::class,
    //
    ActionHandlerBuilderInterface::class    => DefaultActionHandler::class,
    PaymentActionHandler::class => [
        "actionMapper"  => [
            PaymentCreateAction::class      => PaymentCreateActionHandler::class
        ]
    ],
    InjectorRepositoryAInterface::class     => InjectorRepositoryA::class,
    InjectorRepositoryBInterface::class     => InjectorRepositoryB::class,
];