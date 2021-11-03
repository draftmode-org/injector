<?php

namespace Terrazza\Component\Injector\Tests\Examples\Native;

use Psr\Log\LoggerInterface;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryBInterface;

class InjectorUseCaseB implements InjectorUseCaseBInterface {
    private LoggerInterface $logger;
    private InjectorRepositoryBInterface $repository;
    public function __construct(LoggerInterface $logger, InjectorRepositoryBInterface $repository) {
        $this->repository   = $repository;
        $this->logger       = $logger;
        $logger             = $logger->withMethod(__METHOD__);
        $logger->debug("");

    }

    function handle() : void {
        $logger             = $this->logger->withMethod(__METHOD__);
        $logger->debug("");
        $this->repository->handle();
    }
}