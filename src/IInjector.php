<?php
namespace singleframe\Injector;
use Psr\Container\ContainerInterface;
use singleframe\Log\ILogger;

interface IInjector extends ContainerInterface {
    public function get($id, array $arguments=null) : object;
    public function getLogger() : ILogger;
    public function push(string $className, $argument) : void;
}