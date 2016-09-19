<?php
namespace PM\ChainCommandBundle\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class AfterChainProcessingEvent
 *
 * @package PM\ChainCommandBundle\Event
 */
class AfterChainProcessingEvent extends ConsoleEvent
{
    const EVENT_ALIAS = 'chaincommand.after_chain_processing';

    /**
     * AfterChainProcessingEvent constructor.
     *
     * @param Command        $command
     * @param InputInterface $input
     * @param BufferedOutput $output
     */
    public function __construct(Command $command, InputInterface $input, BufferedOutput $output)
    {
        parent::__construct($command, $input, $output);
    }
}
