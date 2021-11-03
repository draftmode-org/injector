<?php
namespace Terrazza\Component\Injector\Tests\Examples\CommandBus\Action\Payment;
use Psr\Log\LoggerInterface;
use Terrazza\Component\Injector\ActionHandlerInterface;
use Terrazza\Component\Injector\ActionInterface;
use Terrazza\Component\Injector\Tests\Application\Domain\Model\Payment;
use Terrazza\Component\Injector\Tests\Examples\CommandBus\Bridge\Payment\PaymentCreateAction;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryAInterface;

/**
 * @implements ActionHandlerInterface<PaymentCreateAction>
 */
class PaymentCreateActionHandler implements ActionHandlerInterface {
    private LoggerInterface $logger;
    private InjectorRepositoryAInterface $repository;
    public function __construct(LoggerInterface $logger, InjectorRepositoryAInterface $repository) {
        $this->repository = $repository;
        $this->logger       = $logger;
        $logger             = $logger->withMethod(__METHOD__);
        $logger->debug("");
    }

    /**
     * @param PaymentCreateAction $action
     * @return Payment|null
     */
    function execute(ActionInterface $action) :?Payment {
        $logger             = $this->logger->withMethod(__METHOD__);
        $logger->debug("");
        return Payment::create(
            date("U"),
            $action->getAmount()
        );
    }
}