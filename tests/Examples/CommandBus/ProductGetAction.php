<?php
namespace Terrazza\Component\Injector\Tests\Examples\CommandBus;
use Terrazza\Component\Injector\ActionInterface;

class ProductGetAction implements ActionInterface {
    private string $view;
    public function __construct(string $view) {
        echo __METHOD__.PHP_EOL;
        $this->view = $view;
    }

    function getView() : string {
        echo __METHOD__.PHP_EOL;
        return $this->view;
    }
}