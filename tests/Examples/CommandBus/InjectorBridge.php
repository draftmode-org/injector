<?php
namespace Terrazza\Component\Injector\Tests\Examples\CommandBus;
use Psr\Log\LoggerInterface;
use Terrazza\Component\Injector\ActionHandlerInterface;

class InjectorBridge {
    private LoggerInterface $logger;
    private ActionHandlerInterface $actionHandler;
    public function __construct(LoggerInterface $logger, ActionHandlerInterface $actionHandler) {
        $this->actionHandler = $actionHandler;
        $this->logger   = $logger;
        $logger         = $logger->withMethod(__METHOD__);
        $logger->debug("");
    }

    function getProduct() : string {
        $logger         = $this->logger->withMethod(__METHOD__);
        $logger->debug("");
        $action         = new ProductGetAction("view");
        return $this->actionHandler->execute($action);
    }

    function saveProduct() : int {
        $logger         = $this->logger->withMethod(__METHOD__);
        $logger->debug("");
        $action         = new ProductSaveAction(12);
        return $this->actionHandler->execute($action);
    }
}