<?php
namespace Terrazza\Component\Injector\Tests\Examples\Native;

use Psr\Log\LoggerInterface;
use Terrazza\Component\Injector\Tests\Application\Domain\Model\Payment;

class InjectorBridge  {
    private LoggerInterface $logger;
    private InjectorUseCaseAInterface $useCaseA;
    private InjectorUseCaseBInterface $useCaseB;
    public function __construct(LoggerInterface $logger,
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
     * @return Payment|null
     */
    function createPayment(float $amount) :?Payment {
        $logger         = $this->logger->withMethod(__METHOD__);
        $logger->debug("");
        return $this->useCaseA->createPayment($amount);
    }
}