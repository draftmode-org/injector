<?php
namespace Terrazza\Component\Injector\Tests\Examples\Native;
use Psr\Log\LoggerInterface;
use Terrazza\Component\Injector\Tests\Application\Domain\Model\Payment;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryAInterface;

class InjectorUseCaseA implements InjectorUseCaseAInterface {
    private LoggerInterface $logger;
    private InjectorRepositoryAInterface $repository;
    public function __construct(LoggerInterface $logger, InjectorRepositoryAInterface $repository) {
        $this->repository   = $repository;
        $this->logger       = $logger;
        $logger             = $logger->withMethod(__METHOD__);
        $logger->debug("");
    }

    function createPayment(float $amount) : Payment {
        $logger             = $this->logger->withMethod(__METHOD__);
        $logger->debug("");
        $this->repository->handle();
        return (Payment::create(date("U"), $amount));
    }
}