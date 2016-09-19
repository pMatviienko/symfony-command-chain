<?php
namespace PM\ChainCommandBundle\EventSubscriber;

use PM\ChainCommandBundle\Event\AfterChainMainCommandEvent;
use PM\ChainCommandBundle\Event\AfterChainProcessingEvent;
use PM\ChainCommandBundle\Event\BeforeChainMainCommandEvent;
use PM\ChainCommandBundle\Event\BeforeChainProcessingEvent;
use PM\ChainCommandBundle\Service\CommandChainManagerService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ChainLoggingSubscriber. Logging chain processing via events.
 *
 * @package PM\ChainCommandBundle\Event
 */
class ChainLoggingSubscriber implements EventSubscriberInterface
{
    const MASTER_COMMAND_LOG_MESSAGE = '%s is a master command of a command chain that has registered member commands';
    const SUBSEQUENT_COMMAND_LOG_MESSAGE = '%s registered as a member of %s command chain';
    const EXECUTING_MAIN_COMMAND_LOG_MESSAGE = 'Executing %s command itself first:';
    const CHAIN_STARTED_LOG_MESSAGE = 'Executing %s chain members:';
    const CHAIN_FINISHED_LOG_MESSAGE = 'Execution of %s chain completed.';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CommandChainManagerService
     */
    private $chainManager;

    /**
     * ConsoleCommandSubscriber constructor.
     *
     * @param CommandChainManagerService $chainManager
     * @param LoggerInterface            $logger
     */
    public function __construct(CommandChainManagerService $chainManager, LoggerInterface $logger)
    {
        $this->chainManager = $chainManager;
        $this->logger = $logger;
    }

    /**
     * Returns event names to subscribe to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            BeforeChainMainCommandEvent::EVENT_ALIAS => 'onBeforeChainMainCommand',
            AfterChainMainCommandEvent::EVENT_ALIAS  => 'onAfterChainMainCommand',
            BeforeChainProcessingEvent::EVENT_ALIAS  => 'onBeforeChainProcessing',
            AfterChainProcessingEvent::EVENT_ALIAS   => 'onAfterChainProcessing',
        ];
    }

    /**
     * @param BeforeChainMainCommandEvent $event
     */
    public function onBeforeChainMainCommand(BeforeChainMainCommandEvent $event)
    {
        $chain = $this->chainManager->getCommandNameChain($event->getCommand()->getName());

        $this->logger->info(sprintf(self::MASTER_COMMAND_LOG_MESSAGE, $event->getCommand()->getName()));
        foreach ($chain as $chainedCommandName) {
            $this->logger->info(
                sprintf(self::SUBSEQUENT_COMMAND_LOG_MESSAGE, $chainedCommandName, $event->getCommand()->getName())
            );
        }

        $this->logger->info(sprintf(self::EXECUTING_MAIN_COMMAND_LOG_MESSAGE, $event->getCommand()->getName()));
    }

    /**
     * @param AfterChainMainCommandEvent $event
     */
    public function onAfterChainMainCommand(AfterChainMainCommandEvent $event)
    {
        $commandOutput = clone $event->getOutput();
        $this->logger->info(trim($commandOutput->fetch()));
    }

    /**
     * @param BeforeChainProcessingEvent $event
     */
    public function onBeforeChainProcessing(BeforeChainProcessingEvent $event)
    {
        $this->logger->info(sprintf(self::CHAIN_STARTED_LOG_MESSAGE, $event->getCommand()->getName()));
    }

    /**
     * @param AfterChainProcessingEvent $event
     */
    public function onAfterChainProcessing(AfterChainProcessingEvent $event)
    {
        $commandOutput = clone $event->getOutput();
        $this->logger->info(trim($commandOutput->fetch()));
        $this->logger->info(sprintf(self::CHAIN_FINISHED_LOG_MESSAGE, $event->getCommand()->getName()));
    }
}
