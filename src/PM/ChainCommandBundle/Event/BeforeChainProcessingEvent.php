<?php
namespace PM\ChainCommandBundle\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class BeforeChainProcessingEvent
 *
 * @package PM\ChainCommandBundle\Event
 */
class BeforeChainProcessingEvent extends ConsoleEvent
{
    const EVENT_ALIAS = 'chaincommand.before_chain_processing';

    /**
     * BeforeChainProcessingEvent constructor.
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
