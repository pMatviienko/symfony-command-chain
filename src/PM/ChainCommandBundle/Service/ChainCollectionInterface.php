<?php
namespace PM\ChainCommandBundle\Service;

use PM\ChainCommandBundle\Exception\Service\ChainCollectionService\NotFoundInChainsException;
use PM\ChainCommandBundle\Exception\Service\ChainCollectionService\RuntimeException;
use Symfony\Component\Console\Command\Command;

interface ChainCollectionInterface
{
    /**
     * Gets a chain as array of commands by main chain command name.
     *
     * @param string $commandName
     *
     * @return Command
     *
     * @throws RuntimeException|\RuntimeException in case if command have no chain.
     */
    public function getChain($commandName);

    /**
     * Get main command names for all registered chains.
     *
     * @return array
     */
    public function getMainCommandNames();

    /**
     * Gets next command in chain by previous commandName.
     *
     * @param string $commandName
     *
     * @return Command
     */
    public function getChainedCommandFor($commandName);

    /**
     * Checking if command already have chain.
     *
     * @param string $commandName
     *
     * @return boolean
     */
    public function hasChainedCommands($commandName);

    /**
     * Gets a main command name that passed command name is chained to.
     *
     * @param string $commandName Command name to get main command for.
     *
     * @return string
     *
     * @throws NotFoundInChainsException|\RuntimeException in case if provided command not present in any chain.
     */
    public function getChainMainCommand($commandName);

    /**
     * Checking is command already chained to any other command
     *
     * @param string $commandName Command name to check.
     *
     * @return boolean
     */
    public function isCommandChained($commandName);

    /**
     * Attaching chained command to main command. If main command have no chain, chain would be created.
     *
     * @param Command $commandToAttach Command instance to attach.
     * @param string  $mainCommand     Main command name to attach to.
     *
     * @return void
     */
    public function attachCommandTo(Command $commandToAttach, $mainCommand);
}
