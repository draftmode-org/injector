<?php
namespace Terrazza\Component\Injector\Tests\Examples\Native;
use Terrazza\Component\Injector\Tests\Application\Domain\Model\Payment;

interface InjectorUseCaseAInterface {
    function createPayment(float $amount) : Payment;
}