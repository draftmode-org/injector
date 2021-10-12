<?php
namespace Terrazza\Component\Injector\Tests\Examples\CommandBus;
use Terrazza\Component\Injector\ActionInterface;

class ProductSaveAction implements ActionInterface {
    private int $id;
    public function __construct(int $id) {
        var_dump(__NAMESPACE__."->__construct()");
        $this->id = $id;
    }

    function getId() : int {
        var_dump(__NAMESPACE__."->getId()");
        return $this->id;
    }
}