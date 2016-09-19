<?php
namespace PM\ChainCommandBundle\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class AfterChainMainCommandEvent
 *
 * @package PM\ChainCommandBundle\Event
 */
class AfterChainMainCommandEvent extends ConsoleEvent
{
    const EVENT_ALIAS = 'chaincommand.after_chain_main';

    /**
     * AfterChainMainCommandEvent constructor.
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
