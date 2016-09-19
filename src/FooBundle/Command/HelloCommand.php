<?php
namespace FooBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HelloCommand. Sample command to show command chain functionality.
 *
 * @package FooBundle\Command
 */
class HelloCommand extends Command
{
    /**
     * Setting up
     */
    protected function configure()
    {
        $this
            ->setName('foo:hello')
            ->setDescription('Sample command from foo bundle');
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
        $output->writeln('Hello from Foo!');
    }
}