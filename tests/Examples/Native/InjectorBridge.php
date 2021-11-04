<?php
namespace Terrazza\Component\Injector\Tests\Examples\Native;


use Terrazza\Component\Injector\Tests\Application\Domain\Payment\PaymentModel;
use Terrazza\Component\Logger\LogInterface;

class InjectorBridge  {
    private LogInterface $logger;
    private InjectorUseCaseAInterface $useCaseA;
    private InjectorUseCaseBInterface $useCaseB;
    public function __construct(LogInterface              $logger,
                                InjectorUseCaseAInterface $useCaseA,
                                InjectorUseCaseBInterface $useCaseB) {
        $this->useCaseA = $useCaseA;
        $this->useCaseB = $useCaseB;
        $this->logger   = $logger;
        $logger         = $logger->withMethod(__METHOD__);
        $logger->debug("");
    }

    /**
     * @param float $amount
     * @return PaymentModel|null
     */
    function createPayment(float $amount) :?PaymentModel {
        $logger         = $this->logger->withMethod(__METHOD__);
        $logger->debug("");
        return $this->useCaseA->createPayment($amount);
    }
}