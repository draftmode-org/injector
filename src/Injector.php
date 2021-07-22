<?php

namespace singleframe\Injector;

use ReflectionNamedType;
use singleframe\Injector\Exception\InjectorException;
use singleframe\Injector\Exception\InjectorLoadMappingException;
use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;
use singleframe\Log\ILogger;
use Throwable;

class Injector implements IInjector {

    /**
     * @var array<string, object>
     */
    private array $containerCache                   = [];

    /**
     * @var string
     */
	private string $classMappingFile;

    /**
     * @var ILogger
     */
	private ILogger $logger;

    /**
     * @var array<string, string|callable|array<string, mixed>>|null
     */
	private ?array $mapping=null;

    /**
     * Injector constructor.
     * @param string $classMappingFile
     * @param ILogger $logger
     */
	public function __construct(string $classMappingFile, ILogger $logger) {
		$this->classMappingFile                     = $classMappingFile;
		$this->logger                               = $logger;
		//
        $this->push(IInjector::class, $this);
        $this->push(ILogger::class, $logger);
	}

    /**
     * @return array<string, string|callable|array<string, mixed>>
     */
	private function loadMapping() : array {
	    if (is_null($this->mapping)) {
            try {
                if (file_exists($this->classMappingFile)) {
                    $mapping                        = require_once($this->classMappingFile);
                    $this->mapping                  = $mapping;
                } else {
                    throw new InjectorLoadMappingException($this->classMappingFile." does not exists");
                }
            } catch (Throwable $exception) {
                throw new InjectorLoadMappingException($this->classMappingFile." could not be loaded", $exception);
            }
        }
	    return $this->mapping;
    }

    /**
     * @param string $mappingKey
     * @return string|callable|array|null
     */
    private function getMapping(string $mappingKey) {
        $mapping                                    = $this->loadMapping();
        if (array_key_exists($mappingKey, $mapping)) {
            return $mapping[$mappingKey];
        }
        return null;
    }

	public function __destruct() {}

	public function getLogger() : ILogger {
	    return $this->logger;
    }

	public function push(string $className, $argument) : void {
	    $this->containerCache[$className]           = $argument;
    }

    /**
     * @param ReflectionFunctionAbstract $method
     * @param array|null $extraMapping
     * @return array
     * @throws ReflectionException
     */
	private function getMethodArgs(ReflectionFunctionAbstract $method, array $extraMapping=null): array {
		$args                                       = [];
		foreach($method->getParameters() as $parameter) {
            $paramKey                               = $parameter->getName();
            $isVariadic                             = $parameter->isVariadic();
            $resultArray                            = [];
            $type                                   = $parameter->getType();
            $result                                 = null;
            if (($type instanceof ReflectionNamedType) && !$type->isBuiltin()) {
                $name                               = $type->getName();
                if ($name === self::class) {
                    $result                         = $this;
                } else {
                    if ($extraMapping && array_key_exists($paramKey, $extraMapping)) {
                        $result                     = $this->get($name, $extraMapping[$paramKey]);
                    } elseif ($extraMapping && count($extraMapping) && $isVariadic) {
                        $resultArray                = $extraMapping;
                    }
                    else {
                        $result                     = $this->get($name);
                    }
                }
            } else {
                if ($type && !$type->allowsNull()) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $result                    = $parameter->getDefaultValue();
                    } else {
                        $className                 = ($method instanceof ReflectionMethod) ? $method->getDeclaringClass()->getName() : '';
                        $methodName                = $method->getName();
                        if (count($this->idTrace)) {
                            $className              = join(" -> ", $this->idTrace);
                        }
                        throw new InjectorException("parameter [$paramKey] missing for $className::$methodName");
                    }
                }
            }
			if ($isVariadic) {
			    if (count($resultArray)) {
                    $args                           = array_merge($args, array_values($resultArray));
                }
            } else {
			    $this->checkParameterValue($parameter, $result);
                $args[]                             = $result;
            }
		}
		return $args;
	}

	private function checkParameterValue(ReflectionParameter $parameter, $value) : void {}

    /**
     * @param ReflectionClass $class
     * @param array $extraMapping
     * @return array
     * @throws ReflectionException
     */
	private function getClassArgs(ReflectionClass $class, array $extraMapping = []): array {
		$constructor                                = $class->getConstructor();
		if (!$constructor) {
			return [];
		}
		return $this->getMethodArgs($constructor, $extraMapping);
	}

	/**
	 * @param string $className
	 * @param array|null $arguments
	 * @return object
	 */
	private function instantiate(string $className, array $arguments=null): object {
		$additionalContext                          = $arguments ?? [];
		$currentClassName                           = $className;
		try {
			do {
				$redo                               = false;
				$mappingInfo                        = $this->getMapping($currentClassName);
				if (is_callable($mappingInfo)) {
					return $mappingInfo(... $this->getMethodArgs(
						new ReflectionFunction(Closure::fromCallable($mappingInfo)), [
							'className'             => $currentClassName,
						]
					));
				}
				if (is_array($mappingInfo)) {
					$additionalContext              = $mappingInfo;
				}
				if (is_string($mappingInfo)) {
					$currentClassName               = $mappingInfo;
					$redo                           = true;
				}
			} while($redo);

			if (class_exists($currentClassName)) {
				$classInfo                          = new ReflectionClass($currentClassName);
				if ($classInfo->isInterface()) {
					throw new InjectorException("Injector->instantiate(): interface $className cannot be instantiated");
				}
				if ($classInfo->isAbstract()) {
					throw new InjectorException("Injector->instantiate(): abstract class $className cannot be instantiated");
				}
				return $classInfo->newInstanceArgs($this->getClassArgs($classInfo, $additionalContext));
			}
		} catch (ReflectionException $ex) {
		    $this->logger->exception($ex);
			throw new InjectorException("Injector->instantiate(): ReflectionException: ".$ex->getMessage(), $ex->getCode(), $ex);
		}
		throw new InjectorException("Injector->instantiate(): class $currentClassName not found");
	}

	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @param string $id Identifier of the entry to look for.
	 * @param array|null $arguments possible optional arguments
	 *
	 * @return object|null Entry.
	 */

	private array $idTrace=[];
	public function get($id, array $arguments=null) : object {
        $this->idTrace[]                            = $id;
        return $this->containerCache[$id] ??= $this->instantiate($id, $arguments);
	}

	/**
	 * Returns true if the container can return an entry for the given identifier.
	 * Returns false otherwise.
	 *
	 * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
	 * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return bool
	 */
	public function has($id): bool {
		return array_key_exists($id, $this->containerCache) || class_exists($id);
	}
	
}