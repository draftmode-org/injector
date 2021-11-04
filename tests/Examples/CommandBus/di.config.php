<?php

use Terrazza\Component\Injector\ActionHandler\DefaultActionHandler;
use Terrazza\Component\Injector\ActionHandlerBuilderInterface;
use Terrazza\Component\Injector\Tests\Application\Action\Payment\PaymentCreateActionHandler;
use Terrazza\Component\Injector\Tests\Application\Action\Payment\PaymentCreateRequest;
use Terrazza\Component\Injector\Tests\Application\Action\PaymentActionHandler;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryA;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryAInterface;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryB;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryBInterface;

return [
    //
    ActionHandlerBuilderInterface::class    => DefaultActionHandler::class,
    PaymentActionHandler::class => [
        "actionMapper"  => [
            PaymentCreateRequest::class      => PaymentCreateActionHandler::class
        ]
    ],
    InjectorRepositoryAInterface::class     => InjectorRepositoryA::class,
    InjectorRepositoryBInterface::class     => InjectorRepositoryB::class,
];