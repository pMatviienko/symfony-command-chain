<?php
namespace PM\ChainCommandBundle\EventSubscriber;

use PM\ChainCommandBundle\Service\CommandChainManagerService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

/**
 * Class ConsoleCommandSubscriber. Subscribing and listen console events.
 *
 * @package PM\ChainCommandBundle\Event
 */
class ConsoleCommandSubscriber implements EventSubscriberInterface
{
    const DISABLED_COMMAND_MESSAGE = '<error>Error: %s command is a member of %s command chain and cannot be executed on its own.</error>';

    /**
     * @var CommandChainManagerService
     */
    private $chainManager;

    /**
     * @var boolean;
     */
    private $listenerEnabled = true;

    /**
     * ConsoleCommandSubscriber constructor.
     *
     * @param CommandChainManagerService $chainManager
     */
    public function __construct(CommandChainManagerService $chainManager)
    {
        $this->chainManager = $chainManager;
    }

    /**
     * Returns event names to subscribe to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND   => 'onConsoleCommand',
            ConsoleEvents::TERMINATE => 'onConsoleTerminate',
        ];
    }

    /**
     * Before command event.
     *
     * @param ConsoleCommandEvent $event
     *
     * @return bool
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        if ($event->stopPropagation() || !$this->listenerEnabled) {
            return true;
        }

        $command = $event->getCommand();

        if ($this->chainManager->isCommandChained($command->getName())) {
            $event->getOutput()->writeln(
                sprintf(
                    self::DISABLED_COMMAND_MESSAGE,
                    $command->getName(),
                    $this->chainManager->getChainMainCommand($command->getName())
                )
            );
            $event->disableCommand();
            $event->stopPropagation();
        } elseif ($this->chainManager->hasChainedCommands($command->getName())) {
            $this->disableListener();
            $this->chainManager->runChain($command, $event->getInput(), $event->getOutput());
            $this->enableListener();
            $event->disableCommand();
        }
    }

    /**
     * After command event.
     *
     * @param ConsoleTerminateEvent $event
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        if ($event->getExitCode() == 113 && $this->chainManager->hasChainedCommands($event->getCommand()->getName())) {
            $event->stopPropagation();

            return;
        }
    }

    /**
     * Allows to enable onConsoleCommand listener.
     */
    public function enableListener()
    {
        $this->listenerEnabled = true;
    }

    /**
     * Allows to disable onConsoleCommand listener.
     */
    public function disableListener()
    {
        $this->listenerEnabled = false;
    }
}
