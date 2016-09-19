<?php
namespace PM\ChainCommandBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HiCommand. Sample command to show command chain functionality.
 *
 * @package BarBundle\Command
 */
class DebugChainCommand extends ContainerAwareCommand
{
    /**
     * Setting up
     */
    protected function configure()
    {
        $this
            ->setName('debug:chain')
            ->setDescription('Dump all configured chains');
    }

    /**
     * Executing command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \PM\ChainCommandBundle\Service\CommandChainManagerService $chainManager
         */
        $chainManager = $this->getContainer()->get('pm.chaincommandbundle.chain.manager');
        $chains = $chainManager->getCommandNameChains();
        foreach ($chains as $mainCommand => $chain) {
            $output->writeln('[<info>' . $mainCommand . '</info>]-><info>' . implode('</info>-><info>', $chain) . '</info>');
        }
    }
}
