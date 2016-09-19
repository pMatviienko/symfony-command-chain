<?php
namespace PM\ChainCommandBundle\Tests\CommandFixture;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixtureCommandNonZeroExit extends Command
{
    protected function configure()
    {
        $this
            ->setName('command:nonZero');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('main');

        return 113;
    }
}
