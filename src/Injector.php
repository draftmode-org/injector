<?php

namespace Terrazza\Component\Injector;

use Closure;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use Terrazza\Component\Injector\Exception\InjectorException;
use Throwable;

class Injector implements InjectorInterface {
    /**
     * @var float
     */
    private float $runtime;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var array<string, object>
     */
    private array $containerCache                   = [];

    /**
     * @var string|array
     */
    private $classMapping;

    /**
     * @var array<string, string|callable|array<string, mixed>>|null
     */
    private ?array $mapping=null;

    private array $traceKey                         = [];

    /**
     * @param string|array $classMapping
     * @param LoggerInterface $logger
     */
    public function __construct($classMapping, LoggerInterface $logger) {
        $this->classMapping                         = $classMapping;
        $this->logger                               = $logger;
        // push yourself into containerCache
        $this->push(InjectorInterface::class, $this);
        // push logger into containerCache
        $this->push(LoggerInterface::class, $logger);
    }

    /**
     * @param class-string<T> $id
     * @param array|null $arguments
     * @return T
     * @template T
     */
    public function get($id, array $arguments=null) : object {
        $runtime_start                              = microtime(true);
        if (array_key_exists($id, $this->containerCache)) {
            $this->logger->debug("$id from containerCache", ["line" => __LINE__, "method" => __METHOD__]);
            $this->runtime                          = microtime(true) - $runtime_start;
            return $this->containerCache[$id];
        } else {
            $container                              = $this->instantiate($id, $arguments);
            $this->push($id, $container);
            $this->runtime                          = microtime(true) - $runtime_start;
            return $container;
        }
    }

    /**
     * @param object $class
     * @param array $arguments
     * @return array
     */
    public function getArguments(object $class, array $arguments) : array {
        return $arguments;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id): bool {
        return array_key_exists($id, $this->containerCache) || class_exists($id);
    }

    /**
     * @return float
     */
    public function getRuntime() : float {
        return $this->runtime ?: 0;
    }

    /**
     * @return int
     */
    public function getContainerCacheCount() : int {
        return count($this->containerCache);
    }

    /**
     * @param string $traceKey
     */
    private function pushTraceKey(string $traceKey) : void {
        array_push($this->traceKey, $traceKey);
    }

    private function popTraceKey() : void {
        array_pop($this->traceKey);
    }

    /**
     * @return string
     */
    private function getTraceKeys() : string {
        $response                                   = join(".",$this->traceKey);
        return strtr($response, [".[" => "["]);
    }

    private function push(string $className, $argument) : void {
        $this->containerCache[$className]           = $argument;
    }

    private function instantiate(string $className, array $arguments=null): object {
        $this->logger->debug($className . ($arguments ? " with arguments" : " without arguments"), ["line" => __LINE__, "method" => __METHOD__, "arguments" => $arguments]);
        $additionalContext                          = $arguments ?? [];
        $currentClassName                           = $className;
        try {
            do {
                $redo                               = false;
                $mappingInfo                        = $this->getMapping($currentClassName);
                if (is_callable($mappingInfo)) {
                    $this->logger->debug(".mappingInfo.isCallable", ["line" => __LINE__, "method" => __METHOD__]);
                    return $mappingInfo(... $this->getMethodArgs(
                        new ReflectionFunction(Closure::fromCallable($mappingInfo)), [
                            'className'             => $currentClassName,
                        ]
                    ));
                }
                elseif (is_array($mappingInfo)) {
                    $this->logger->debug(".mappingInfo.isArray", ["line" => __LINE__, "method" => __METHOD__]);
                    if (is_array($arguments) && count($arguments)) {
                        $additionalContext          = array_filter($mappingInfo + $arguments);
                    } else {
                        $additionalContext          = $mappingInfo;
                    }
                }
                elseif (is_string($mappingInfo)) {
                    $this->logger->debug(".mappingInfo.isString:$mappingInfo", ["line" => __LINE__, "method" => __METHOD__]);
                    $currentClassName               = $mappingInfo;
                    $redo                           = true;
                }
            } while($redo);

            /*if (!class_exists($currentClassName, false)) {
                var_dump(error_get_last());
                throw new InjectorException("hallo:".$currentClassName);
            }*/
            if (class_exists($currentClassName)) {
                $this->logger->debug("$currentClassName class exists", ["line" => __LINE__, "method" => __METHOD__]);
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
            throw new InjectorException("Injector->instantiate(): ReflectionException: ".$ex->getMessage(), $ex->getCode(), $ex);
        }
        throw new InjectorException("Injector->instantiate(): class $currentClassName not found");
    }

    /**
     * @param string $mappingKey
     * @return string|callable|array|null
     */
    private function getMapping(string $mappingKey) {
        $this->logger->debug("get mapping for $mappingKey", ["line" => __LINE__, "method" => __METHOD__]);
        $mapping                                    = $this->loadMapping();
        if (array_key_exists($mappingKey, $mapping)) {
            $this->logger->debug("mappingKey found by loadMapping", ["line" => __LINE__, "method" => __METHOD__]);
            return $mapping[$mappingKey];
        } else {
            $this->logger->debug("mappingKey not found by loadMapping", ["line" => __LINE__, "method" => __METHOD__]);
            return null;
        }
    }

    /**
     * @return array<string, string|callable|array<string, mixed>>
     * @throws InjectorException
     */
    private function loadMapping() : array {
        if (is_null($this->mapping)) {
            $this->logger->debug("initialize new mapping", ["line" => __LINE__, "method" => __METHOD__]);
            try {
                if (is_array($this->classMapping)) {
                    $this->logger->debug("classMapping is array, provide it as mapping", ["line" => __LINE__, "method" => __METHOD__]);
                    $this->mapping                  = $this->classMapping;
                }
                elseif (is_string($this->classMapping)) {
                    if (file_exists($this->classMapping)) {
                        $this->logger->debug("require classMapping, ".$this->classMapping, ["line" => __LINE__, "method" => __METHOD__]);
                        $mapping                    = require_once($this->classMapping);
                        $this->mapping              = $mapping;
                    } else {
                        throw new InjectorException("loadMapping file " . $this->classMapping . " not found/does not exists");
                    }
                } else {
                    throw new InjectorException("loadMapping classMapping expected string (file), array (mapping), given ".gettype($this->classMapping));
                }
            } catch (InjectorException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                throw new InjectorException("loadMapping could not be loaded", $exception->getCode(), $exception);
            }
        } else {
            $this->logger->debug("mapping already initialzed", ["line" => __LINE__, "method" => __METHOD__]);
        }
        return $this->mapping;
    }

    /**
     * @param ReflectionFunctionAbstract $method
     * @param array|null $extraMapping
     * @return array
     */
    private function getMethodArgs(ReflectionFunctionAbstract $method, array $extraMapping=null): array {
        $this->logger->debug("", ["line" => __LINE__, "method" => __METHOD__, "arguments" => $extraMapping]);
        $args                                       = [];
        foreach($method->getParameters() as $parameter) {
            $paramKey                               = $parameter->getName();
            $this->logger->debug("getArg $paramKey", ["line" => __LINE__, "method" => __METHOD__]);
            $this->pushTraceKey($paramKey);
            if ($extraMapping && array_key_exists($paramKey, $extraMapping)) {
                $this->logger->debug(".use from extraMapping", ["line" => __LINE__, "method" => __METHOD__]);
                $result                             = $extraMapping[$paramKey];
            } else {
                $type                               = $parameter->getType();
                $result                             = null;
                if (($type instanceof ReflectionNamedType) && !$type->isBuiltin()) {
                    $name                           = $type->getName();
                    if ($name === self::class) {
                        $result                     = $this;
                    } else {
                        $result                     = $this->get($name);
                    }
                } else if ($type && !$type->allowsNull()) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $result                    = $parameter->getDefaultValue();
                    } else {
                        throw new InjectorException("parameter ".$this->getTraceKeys()." missing");
                    }
                }
            }
            $args[]                                 = $result;
            $this->popTraceKey();
        }
        return $args;
    }

    /**
     * @param ReflectionClass $class
     * @param array $extraMapping
     * @return array
     */
    private function getClassArgs(ReflectionClass $class, array $extraMapping = []): array {
        $constructor                                = $class->getConstructor();
        if ($constructor) {
            return $this->getMethodArgs($constructor, $extraMapping);
        }
        else {
            $this->logger->debug("class ".$class->getName()." has no constructor", ["line" => __LINE__, "method" => __METHOD__]);
            return [];
        }
    }
}