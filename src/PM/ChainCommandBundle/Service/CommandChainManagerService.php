<?php
namespace PM\ChainCommandBundle\Service;

use PM\ChainCommandBundle\Decorator\CommandDecorator;
use PM\ChainCommandBundle\Event\AfterChainMainCommandEvent;
use PM\ChainCommandBundle\Event\AfterChainProcessingEvent;
use PM\ChainCommandBundle\Event\BeforeChainMainCommandEvent;
use PM\ChainCommandBundle\Event\BeforeChainProcessingEvent;
use PM\ChainCommandBundle\Exception\Service\CommandChainManager\RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class CommandChainManager.
 *
 * @package PM\ChainCommandBundle\Service
 */
class CommandChainManagerService
{

    const Chained_COMMAND_LOG_MESSAGE = '%s registered as a member of %s command chain';
    /**
     * @var ChainCollectionInterface
     */
    private $chains;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * CommandChainManager constructor.
     *
     * @param ChainCollectionInterface $collection
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ChainCollectionInterface $collection,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->chains = $collection;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Allows to chain command to other command by chained command instance.
     *
     * @param string  $mainCommandName
     * @param Command $chainedCommand
     *
     * @return void
     *
     * @throws \RuntimeException in case if main command already have chain or Chained command already a member of chain.
     */
    public function addCommandToChain($mainCommandName, Command $chainedCommand)
    {
        if ($this->chains->isCommandChained($chainedCommand->getName())) {
            throw new RuntimeException(
                'Command "' . $chainedCommand->getName() . '" already chained to "' . $this->chains->getChainMainCommand(
                    $chainedCommand->getName()
                ) . '"'
            );
        }
        if ($this->chains->hasChainedCommands($mainCommandName)) {
            throw new RuntimeException(
                'Command "' . $mainCommandName . '" already have chain. Command "' . $chainedCommand->getName() . '" could not be chained to it'
            );
        }

        $this->chains->attachCommandTo($chainedCommand, $mainCommandName);
    }

    /**
     * Proxy to collection's hasChainedCommands.
     *
     * @see ChainCollectionInterface::hasChainedCommands()
     *
     * @param string $commandName
     *
     * @return bool
     */
    public function hasChainedCommands($commandName)
    {
        return $this->chains->hasChainedCommands($commandName);
    }

    /**
     * Proxy for collection's isCommandChained.
     *
     * @see ChainCollectionInterface::isCommandChained()
     *
     * @param $commandName
     *
     * @return bool
     */
    public function isCommandChained($commandName)
    {
        return $this->chains->isCommandChained($commandName);
    }

    /**
     * Proxy for collection's isCommandChained.
     *
     * @see ChainCollectionInterface::getChainMainCommand()
     *
     * @param $commandName
     *
     * @return string
     *
     * @throws \RuntimeException in case if provided command not present in any chain.
     */
    public function getChainMainCommand($commandName)
    {
        return $this->chains->getChainMainCommand($commandName);
    }

    /**
     * Gets a command names list for chain registered for provided command name.
     *
     * @param string $commandName
     *
     * @return array
     */
    public function getCommandNameChain($commandName)
    {
        $nameList = [];
        foreach ($this->chains->getChain($commandName) as $command) {
            $nameList[] = $command->getName();
        }

        return $nameList;
    }

    /**
     * Gets all registered chains. Main command name is a key.
     *
     * @return array In format ['mainCommandName'=>['chainedCommandName', 'chainedCommandName']]
     */
    public function getCommandNameChains()
    {
        $chains = [];
        foreach ($this->chains->getMainCommandNames() as $mainCommandName) {
            $chains[$mainCommandName] = $this->getCommandNameChain($mainCommandName);
        }

        return $chains;
    }


    /**
     * Running chine of commands.
     *
     * @param Command         $chainMainCommand
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws RuntimeException|\RuntimeException in case if some command return non zero exit code.
     */
    public function runChain(Command $chainMainCommand, InputInterface $input, OutputInterface $output)
    {
        $exitCode = $this->runChainMainCommand($chainMainCommand, $input, $output);
        if ($exitCode != 0) {
            throw new RuntimeException(
                'Main chain command returned non zero exit code. Chain can not be processed.'
            );
        }
        $this->runChainCommands($chainMainCommand, $input, $output);

        return $exitCode;
    }

    /**
     * Running commands in chain.
     *
     * @param Command         $chainMainCommand
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     *
     * @throws RuntimeException|\RuntimeException in case if at least one command in chain returned non zero exit code.
     */
    private function runChainCommands(Command $chainMainCommand, InputInterface $input, OutputInterface $output)
    {
        $chainCommands = $this->chains->getChain($chainMainCommand->getName());
        $commandOutput = new BufferedOutput();
        //Dispatching chain event
        $this->eventDispatcher->dispatch(
            BeforeChainProcessingEvent::EVENT_ALIAS,
            new BeforeChainProcessingEvent($chainMainCommand, $input, $commandOutput)
        );
        foreach ($chainCommands as $command) {
            $decorator = new CommandDecorator($command, $this->eventDispatcher);
            $exitCode = $decorator->run($input, $commandOutput);
            if ($exitCode != 0) {
                throw new RuntimeException(
                    'Cannot fully process chain because command "' . $decorator->getName() . '" returned non zero exit code.'
                );
            }
        }
        $this->eventDispatcher->dispatch(
            AfterChainProcessingEvent::EVENT_ALIAS,
            new AfterChainProcessingEvent($chainMainCommand, $input, clone $commandOutput)
        );
        $commandOutputContent = $commandOutput->fetch();
        $output->write($commandOutputContent);
    }

    /**
     * Running main command in chain.
     *
     * @param Command         $chainMainCommand
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    private function runChainMainCommand(Command $chainMainCommand, InputInterface $input, OutputInterface $output)
    {
        $commandOutput = new BufferedOutput();
        //Dispatching chain event
        $this->eventDispatcher->dispatch(
            BeforeChainMainCommandEvent::EVENT_ALIAS,
            new BeforeChainMainCommandEvent($chainMainCommand, $input, $commandOutput)
        );

        //Running command
        $exitCode = $chainMainCommand->run($input, $commandOutput);
        //Dispatching chain event
        $this->eventDispatcher->dispatch(
            AfterChainMainCommandEvent::EVENT_ALIAS,
            new AfterChainMainCommandEvent($chainMainCommand, $input, clone $commandOutput)
        );
        //Fetching buffer content
        $commandOutputContent = $commandOutput->fetch();
        //writing command output to stream passed to method.
        $output->write($commandOutputContent);
        //Dispatching kernel event
        $this->eventDispatcher->dispatch(
            'console.terminate',
            new ConsoleTerminateEvent($chainMainCommand, $input, $output, $exitCode)
        );

        return $exitCode;
    }
}
