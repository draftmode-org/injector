<?php
namespace Terrazza\Component\Injector;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class InjectorException extends RuntimeException implements ContainerExceptionInterface {}