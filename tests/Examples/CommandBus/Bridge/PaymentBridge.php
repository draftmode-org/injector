<?php
namespace Terrazza\Component\Injector\Tests\Examples\CommandBus\Bridge;
use Psr\Log\LoggerInterface;
use Terrazza\Component\Injector\ActionHandlerInterface;
use Terrazza\Component\Injector\Tests\Application\Domain\Model\Payment;
use Terrazza\Component\Injector\Tests\Examples\CommandBus\Action\PaymentActionHandler;
use Terrazza\Component\Injector\Tests\Examples\CommandBus\Bridge\Payment\PaymentCreateAction;

class PaymentBridge {
    private LoggerInterface $logger;
    private ActionHandlerInterface $actionHandler;
    public function __construct(LoggerInterface $logger, PaymentActionHandler $actionHandler) {
        $this->actionHandler = $actionHandler;
        $this->logger       = $logger;
        $logger             = $logger->withMethod(__METHOD__);
        $logger->debug("");
    }

    /**
     * @param float $amount
     * @return Payment|null
     */
    function createPayment(float $amount) :?Payment {
        $logger             = $this->logger->withMethod(__METHOD__);
        $logger->debug("");
        $action = new PaymentCreateAction($amount);
        return $this->actionHandler->execute($action);
    }
}