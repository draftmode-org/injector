<?php
namespace Terrazza\Component\Injector\Tests\Application\Bridge;
use Terrazza\Component\Injector\ActionHandlerInterface;
use Terrazza\Component\Injector\Tests\Application\Action\Payment\PaymentCreateRequest;
use Terrazza\Component\Injector\Tests\Application\Action\PaymentActionHandler;
use Terrazza\Component\Injector\Tests\Application\Domain\Payment\PaymentModel;
use Terrazza\Component\Logger\LogInterface;

class PaymentBridge {
    private LogInterface $logger;
    private ActionHandlerInterface $actionHandler;
    public function __construct(LogInterface $logger, PaymentActionHandler $actionHandler) {
        $this->actionHandler = $actionHandler;
        $this->logger       = $logger;
        $logger             = $logger->withMethod(__METHOD__);
        $logger->debug("");
    }

    /**
     * @param float $amount
     * @return PaymentModel|null
     */
    function createPayment(float $amount) :?PaymentModel {
        $logger             = $this->logger->withMethod(__METHOD__);
        $logger->debug("");
        $action             = new PaymentCreateRequest($amount);
        return $this->actionHandler->execute($action);
    }
}