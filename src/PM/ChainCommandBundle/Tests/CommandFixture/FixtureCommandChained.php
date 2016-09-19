<?php
namespace PM\ChainCommandBundle\Tests\CommandFixture;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixtureCommandChained extends Command
{
    protected function configure()
    {
        $this
            ->setName('command:chained');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('chained');
    }
}
