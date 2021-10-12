<?php
namespace Terrazza\Component\Injector\Tests\Examples\CommandBus;
use Terrazza\Component\Injector\ActionHandlerInterface;

class InjectorBridge {
    private ActionHandlerInterface $actionHandler;
    public function __construct(ActionHandlerInterface $actionHandler) {
        $this->actionHandler = $actionHandler;
        echo __METHOD__.PHP_EOL;
    }

    function getProduct() : string {
        echo __METHOD__.PHP_EOL;
        $action = new ProductGetAction("view");
        return $this->actionHandler->execute($action);
    }

    function saveProduct() : int {
        echo __METHOD__.PHP_EOL;
        $action = new ProductSaveAction(12);
        return $this->actionHandler->execute($action);
    }
}