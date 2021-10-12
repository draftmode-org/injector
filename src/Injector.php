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

    /**
     * @param class-string<T> $id
     * @param array|null $arguments
     * @return T
     * @template T
     */
    public function get($id, array $arguments=null) : object {
        $logger                                     = $this->logger->withMethod(__METHOD__);
        if (array_key_exists($id, $this->containerCache)) {
            $logger->debug("get $id from containerCache", ["line" => __LINE__]);
            return $this->containerCache[$id];
        } else {
            $logger->debug("call instantiate for $id", ["line" => __LINE__]);
            return $this->instantiate($id, $arguments);
        }
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id): bool {
        return array_key_exists($id, $this->containerCache) || class_exists($id);
    }

    private function push(string $className, $argument) : void {
        $this->containerCache[$className]           = $argument;
    }

    private function instantiate(string $className, array $arguments=null): object {
        $logger                                     = $this->logger->withMethod(__METHOD__);
        $logger->debug("className: $className", ["line" => __LINE__, "arguments" => $arguments]);
        $additionalContext                          = $arguments ?? [];
        $currentClassName                           = $className;
        try {
            do {
                $redo                               = false;
                $mappingInfo                        = $this->getMapping($currentClassName);
                $logger->debug(".hasMappingInfo", ["line" => __LINE__]);
                if (is_callable($mappingInfo)) {
                    $logger->debug(".mappingInfo.isCallable", ["line" => __LINE__]);
                    return $mappingInfo(... $this->getMethodArgs(
                        new ReflectionFunction(Closure::fromCallable($mappingInfo)), [
                            'className'             => $currentClassName,
                        ]
                    ));
                }
                if (is_array($mappingInfo)) {
                    $logger->debug(".mappingInfo.isArray", ["line" => __LINE__]);
                    $additionalContext              = $mappingInfo;
                }
                if (is_string($mappingInfo)) {
                    $logger->debug(".mappingInfo.isString:$mappingInfo", ["line" => __LINE__]);
                    $currentClassName               = $mappingInfo;
                    $redo                           = true;
                }
                $logger->debug(".redo:".($redo ? "yes" : "no"), ["line" => __LINE__]);
            } while($redo);

            if (class_exists($currentClassName)) {
                $logger->debug("$currentClassName class exists", ["line" => __LINE__]);
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
        $logger                                     = $this->logger->withMethod(__METHOD__);
        $logger->debug("mappingKey:$mappingKey", ["line" => __LINE__]);
        $mapping                                    = $this->loadMapping();
        if (array_key_exists($mappingKey, $mapping)) {
            return $mapping[$mappingKey];
        }
        return null;
    }

    /**
     * @return array<string, string|callable|array<string, mixed>>
     * @throws InjectorException
     */
    private function loadMapping() : array {
        $logger                                     = $this->logger->withMethod(__METHOD__);
        $logger->debug("", ["line" => __LINE__]);
        if (is_null($this->mapping)) {
            try {
                if (is_array($this->classMapping)) {
                    $this->mapping                  = $this->classMapping;
                }
                elseif (is_string($this->classMapping)) {
                    if (file_exists($this->classMapping)) {
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
        }
        return $this->mapping;
    }

    /**
     * @param ReflectionFunctionAbstract $method
     * @param array|null $extraMapping
     * @return array
     */
    private function getMethodArgs(ReflectionFunctionAbstract $method, array $extraMapping=null): array {
        $logger                                     = $this->logger->withMethod(__METHOD__);
        $logger->debug("", ["line" => __LINE__, "arguments" => $extraMapping]);
        $args                                       = [];
        foreach($method->getParameters() as $parameter) {
            $paramKey                               = $parameter->getName();
            $logger->debug("getArg $paramKey", ["line" => __LINE__]);
            $this->pushTraceKey($paramKey);
            if ($extraMapping && array_key_exists($paramKey, $extraMapping)) {
                $logger->debug(".use from extraMapping", ["line" => __LINE__]);
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
        $logger                                     = $this->logger->withMethod(__METHOD__);
        $logger->debug("class: ".$class->getName(), ["line" => __LINE__, "arguments" => $extraMapping]);
        $constructor                                = $class->getConstructor();
        if (!$constructor) {
            return [];
        }
        return $this->getMethodArgs($constructor, $extraMapping);
    }
}