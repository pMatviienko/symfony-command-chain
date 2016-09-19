<?php
namespace BarBundle\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HiCommand. Sample command to show command chain functionality.
 *
 * @package BarBundle\Command
 */
class HiCommand extends Command
{
    /**
     * Setting up
     */
    protected function configure()
    {
        $this
            ->setName('bar:hi')
            ->setDescription('Sample command from bar bundle');
    }

    /**
     * Executing command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hi from Bar!');
    }
}