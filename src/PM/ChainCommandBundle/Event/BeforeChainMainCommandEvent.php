<?php
namespace PM\ChainCommandBundle\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class BeforeChainMainCommandEvent
 *
 * @package PM\ChainCommandBundle\Event
 */
class BeforeChainMainCommandEvent extends ConsoleEvent
{
    const EVENT_ALIAS = 'chaincommand.before_chain_main';

    /**
     * BeforeChainMainCommandEvent constructor.
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
