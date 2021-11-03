<?php
namespace Terrazza\Component\Injector;
use Psr\Container\ContainerInterface;

interface InjectorInterface extends ContainerInterface {
    /**
     * @param class-string<T> $id
     * @param array|null $arguments
     * @return T
     * @template T
     */
    public function get($id, array $arguments=null) : object;

    /**
     * @return float
     */
    public function getRuntime() : float;

    /**
     * @return int
     */
    public function getContainerCacheCount() : int;
}