<?php
namespace Terrazza\Injector;
use Psr\Container\ContainerInterface;

interface IInjector extends ContainerInterface {
    public function get($id, array $arguments=null) : object;
    public function push(string $className, $argument) : void;
}