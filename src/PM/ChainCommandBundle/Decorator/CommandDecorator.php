<?php
namespace PM\ChainCommandBundle\Decorator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandDecorator
{
    /**
     * @var Command
     */
    private $command;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * CommandDecorator constructor.
     *
     * @param Command                  $command
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Command $command, EventDispatcherInterface $eventDispatcher)
    {
        $this->command = $command;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Proxy to command's getName method.
     *
     * @return string
     */
    public function getName()
    {
        return $this->command->getName();
    }

    /**
     * Decorator for command's run method.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return OutputInterface
     *
     * @throws \LogicException
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->eventDispatcher->dispatch(ConsoleEvents::COMMAND, new ConsoleCommandEvent($this->command, $input, $output));
        $exitCode = $this->command->run($input, $output);
        $this->eventDispatcher->dispatch(ConsoleEvents::TERMINATE, new ConsoleTerminateEvent($this->command, $input, $output, $exitCode));

        return $exitCode;
    }
}
