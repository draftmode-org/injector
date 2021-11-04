<?php
namespace Terrazza\Component\Injector\Tests\Application\Action\Payment;
use Terrazza\Component\Injector\ActionHandlerInterface;
use Terrazza\Component\Injector\ActionInterface;
use Terrazza\Component\Injector\Tests\Application\Domain\Payment\PaymentModel;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryAInterface;
use Terrazza\Component\Logger\LogInterface;

/**
 * @implements ActionHandlerInterface<PaymentCreateRequest>
 */
class PaymentCreateActionHandler implements ActionHandlerInterface {
    private LogInterface $logger;
    private InjectorRepositoryAInterface $repository;
    public function __construct(LogInterface $logger, InjectorRepositoryAInterface $repository) {
        $this->repository = $repository;
        $this->logger       = $logger;
        $logger             = $logger->withMethod(__METHOD__);
        $logger->debug("");
    }

    /**
     * @param PaymentCreateRequest $action
     * @return PaymentModel|null
     */
    function execute(ActionInterface $action) :?PaymentModel {
        $logger             = $this->logger->withMethod(__METHOD__);
        $logger->debug("");
        return PaymentModel::create(
            date("U"),
            $action->getAmount()
        );
    }
}