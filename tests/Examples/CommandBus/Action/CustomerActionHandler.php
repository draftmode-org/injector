<?php
namespace Terrazza\Component\Injector\Tests\Examples\CommandBus\Action;
use Terrazza\Component\Injector\ActionHandlerBuilderInterface;
use Terrazza\Component\Injector\ActionHandlerInterface;
use Terrazza\Component\Injector\ActionInterface;

class CustomerActionHandler  implements ActionHandlerInterface {
    private ActionHandlerInterface $actionHandler;
    public function __construct(array $actionMapper, ActionHandlerBuilderInterface $actionHandlerBuilder) {
        $this->actionHandler                        = $actionHandlerBuilder->withMapper($actionMapper);
    }

    /**
     * @template RR of mixed
     * @param ActionInterface<RR> $action
     * @return RR
     */
    function execute(ActionInterface $action) {
        return $this->actionHandler->execute($action);
    }
}