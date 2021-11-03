<?php
namespace Terrazza\Component\Injector\Tests\Examples\CommandBus\Bridge\Payment;
use Terrazza\Component\Injector\ActionInterface;

class PaymentCreateAction implements ActionInterface {
    private float $amount;
    public function __construct(float $amount) {
        $this->amount = $amount;
    }

    function getAmount() : float {
        return $this->amount;
    }
}