<?php
namespace Terrazza\Component\Injector\Tests\Examples\Native;
use Terrazza\Component\Injector\Tests\Application\Domain\Payment\PaymentModel;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryAInterface;
use Terrazza\Component\Logger\LogInterface;

class InjectorUseCaseA implements InjectorUseCaseAInterface {
    private LogInterface $logger;
    private InjectorRepositoryAInterface $repository;
    public function __construct(LogInterface $logger, InjectorRepositoryAInterface $repository) {
        $this->repository   = $repository;
        $this->logger       = $logger;
        $logger             = $logger->withMethod(__METHOD__);
        $logger->debug("");
    }

    function createPayment(float $amount) : PaymentModel {
        $logger             = $this->logger->withMethod(__METHOD__);
        $logger->debug("");
        $this->repository->handle();
        return (PaymentModel::create(date("U"), $amount));
    }
}