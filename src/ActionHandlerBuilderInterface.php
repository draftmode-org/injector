<?php
namespace Terrazza\Component\Injector;

interface ActionHandlerBuilderInterface {
    public function withMapper(array $actionMapper) : ActionHandlerInterface;
}