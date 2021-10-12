<?php
namespace Terrazza\Component\Injector\Tests\Examples\CommandBus;
use Terrazza\Component\Injector\ActionHandlerInterface;
use Terrazza\Component\Injector\ActionInterface;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryAInterface;

/**
 * @implements ActionHandlerInterface<ProductGetAction>
 */
class ProductGetActionHandler implements ActionHandlerInterface {
    private InjectorRepositoryAInterface $repository;
    public function __construct(InjectorRepositoryAInterface $repository) {
        $this->repository = $repository;
        echo __METHOD__.PHP_EOL;
    }

    /**
     * @param ProductGetAction $action
     * @return string
     */
    function execute(ActionInterface $action) : string {
        echo __METHOD__.PHP_EOL;
        return $action->getView();
    }
}