<?php
namespace Terrazza\Component\Injector\Tests\Examples\Native;

use Terrazza\Component\Injector\Tests\Application\Domain\Payment\PaymentModel;

interface InjectorUseCaseAInterface {
    function createPayment(float $amount) : PaymentModel;
}