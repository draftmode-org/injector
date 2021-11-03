<?php
namespace Terrazza\Component\Injector\Tests\Examples;
use Psr\Log\LoggerInterface;

class InjectorRepositoryB implements InjectorRepositoryBInterface {
    private LoggerInterface $logger;
    public function __construct(LoggerInterface $logger) {
        $this->logger   = $logger;
        $logger         = $logger->withMethod(__METHOD__);
        $logger->debug("");
    }

    public function handle() : void {
        $logger         = $this->logger->withMethod(__METHOD__);
        $logger->debug("");
    }
}