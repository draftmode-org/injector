<?php
namespace Terrazza\Injector\Exception;
use Throwable;

class InjectorLoadMappingException extends InjectorException {
    public function __construct(string $injectorMappingFile = "", Throwable $previous = null) {
        parent::__construct("injectorMappingFile $injectorMappingFile could not be loaded", 500, $previous);
    }
}