<?php

namespace Terrazza\Component\Injector;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Throwable;

class Injector implements InjectorInterface {
    /**
     * @var array<string, object>
     */
    private array $containerCache                   = [];

    /**
     * @var string
     */
    private string $classMappingFile;

    /**
     * @var array<string, string|callable|array<string, mixed>>|null
     */
    private ?array $mapping=null;

    /**
     * @var array
     */
    private array $idTrace=[];

    private function print(int $line, string $message) : void {
        print_r(__NAMESPACE__."->".$message." [".$line."]".PHP_EOL);
    }

    public function __construct(string $classMappingFile) {
        $this->classMappingFile                     = $classMappingFile;

        // push yourself into containerCache
        $this->push(InjectorInterface::class, $this);
    }

    /**
     * @param class-string<T> $id
     * @param array|null $arguments
     * @return T
     * @template T
     */
    public function get($id, array $arguments=null) : object {
        $this->print(__LINE__,"get($id)");
        $this->idTrace[]                            = $id;
        return $this->containerCache[$id] ??= $this->instantiate($id, $arguments);
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
        $this->print(__LINE__, "instantiate($className)");
        $additionalContext                          = $arguments ?? [];
        $currentClassName                           = $className;
        try {
            do {
                $redo                               = false;
                $mappingInfo                        = $this->getMapping($currentClassName);
                $this->print(__LINE__, "instantiate(), hasMappingInfo");
                if (is_callable($mappingInfo)) {
                    $this->print(__LINE__, "instantiate(), mappingInfo isCallable");
                    return $mappingInfo(... $this->getMethodArgs(
                        new ReflectionFunction(Closure::fromCallable($mappingInfo)), [
                            'className'             => $currentClassName,
                        ]
                    ));
                }
                if (is_array($mappingInfo)) {
                    $this->print(__LINE__, "instantiate(), mappingInfo isArray");
                    $additionalContext              = $mappingInfo;
                }
                if (is_string($mappingInfo)) {
                    $this->print(__LINE__, "instantiate(), mappingInfo isString");
                    $currentClassName               = $mappingInfo;
                    $redo                           = true;
                }
                $this->print(__LINE__, "instantiate(), redo.".($redo ? "yes" : "no"));
            } while($redo);

            if (class_exists($currentClassName)) {
                $this->print(__LINE__, "instantiate(), class $currentClassName exists");
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
        $this->print(__LINE__, "getMapping($mappingKey)");
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
        $this->print(__LINE__, "loadMapping()");
        if (is_null($this->mapping)) {
            try {
                if (file_exists($this->classMappingFile)) {
                    $mapping = require_once($this->classMappingFile);
                    $this->mapping = $mapping;
                } else {
                    throw new InjectorException("loadMapping file " . $this->classMappingFile . " not found/does not exists");
                }
            } catch (InjectorException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                throw new InjectorException("loadMapping file ".$this->classMappingFile." could not be loaded", 0, $exception);
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
        $this->print(__LINE__, "getMethodArgs(".$method->getName().")");
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
     */
    private function getClassArgs(ReflectionClass $class, array $extraMapping = []): array {
        $this->print(__LINE__,"getClassArgs(".$class->getName().")");
        $constructor                                = $class->getConstructor();
        if (!$constructor) {
            return [];
        }
        return $this->getMethodArgs($constructor, $extraMapping);
    }
}