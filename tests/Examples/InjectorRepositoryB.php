<?php
namespace Terrazza\Component\Injector\Tests\Examples;
use Terrazza\Component\Logger\LogInterface;

class InjectorRepositoryB implements InjectorRepositoryBInterface {
    private LogInterface $logger;
    public function __construct(LogInterface $logger) {
        $this->logger   = $logger;
        $logger         = $logger->withMethod(__METHOD__);
        $logger->debug("");
    }

    public function handle() : void {
        $logger         = $this->logger->withMethod(__METHOD__);
        $logger->debug("");
    }
}