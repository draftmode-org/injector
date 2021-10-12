<?php
namespace Terrazza\Component\Injector;

interface ActionHandlerInterface {
    public function execute(ActionInterface $action);
}