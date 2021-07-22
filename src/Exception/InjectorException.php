<?php
namespace singleframe\Injector\Exception;
use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class InjectorException extends RuntimeException implements ContainerExceptionInterface {}