<?php
namespace Terrazza\Component\Injector\Tests\Examples\CommandBus;
use Terrazza\Component\Injector\ActionHandlerInterface;
use Terrazza\Component\Injector\ActionInterface;
use Terrazza\Component\Injector\Tests\Examples\InjectorRepositoryBInterface;

/**
 * @implements ActionHandlerInterface<ProductSaveAction>
 */
class ProductSaveActionHandler implements ActionHandlerInterface {
    private InjectorRepositoryBInterface $repository;
    public function __construct(InjectorRepositoryBInterface $repository) {
        $this->repository = $repository;
        echo __METHOD__.PHP_EOL;
    }

    /**
     * @param ProductSaveAction $action
     * @return int
     */
    function execute(ActionInterface $action) : int {
        echo __METHOD__.PHP_EOL;
        return $action->getId();
    }
}