<?php

namespace Terrazza\Component\Injector\Tests\Examples\Native;

use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryBInterface;
use Terrazza\Component\Logger\LogInterface;

class InjectorUseCaseB implements InjectorUseCaseBInterface {
    private LogInterface $logger;
    private InjectorRepositoryBInterface $repository;
    public function __construct(LogInterface $logger, InjectorRepositoryBInterface $repository) {
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