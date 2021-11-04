<?php
namespace Terrazza\Component\Injector\Tests\Application\Action\Payment;
use Terrazza\Component\Injector\ActionInterface;

class PaymentCreateRequest implements ActionInterface {
    private float $amount;
    public function __construct(float $amount) {
        $this->amount = $amount;
    }

    function getAmount() : float {
        return $this->amount;
    }
}