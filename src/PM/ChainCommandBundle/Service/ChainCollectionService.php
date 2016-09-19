<?php
namespace PM\ChainCommandBundle\Service;

use PM\ChainCommandBundle\Exception\Service\ChainCollectionService\NotFoundInChainsException;
use PM\ChainCommandBundle\Exception\Service\ChainCollectionService\RuntimeException;
use Symfony\Component\Console\Command\Command;

/**
 * Class ChainCollectionComponent. Processing logics related to collection of chains.
 *
 * @package PM\ChainCommandBundle\Component
 */
class ChainCollectionService implements ChainCollectionInterface
{
    /**
     * @var Command[]
     */
    private $chains = [];

    /**
     * Attaching chained command to main command. If main command have no chain, chain would be created.
     *
     * @param Command $commandToAttach Command instance to attach.
     * @param string  $mainCommand     Main command name to attach to.
     *
     * @return void
     */
    public function attachCommandTo(Command $commandToAttach, $mainCommand)
    {
        if (array_key_exists($mainCommand, $this->chains)) {
            throw new RuntimeException(
                'Command "' . $mainCommand . '" already have chained command "' . $this->chains[$mainCommand]->getName() . '"'
            );
        }

        if($mainCommand == $commandToAttach->getName()){
            throw new RuntimeException(
                'Tried to attach "'.$mainCommand.'" command to itself'
            );
        }

        $this->chains[$mainCommand] = $commandToAttach;
    }

    /**
     * Checking is command already chained to any other command
     *
     * @param string $commandName Command name to check.
     *
     * @return boolean
     */
    public function isCommandChained($commandName)
    {
        foreach ($this->chains as $mainCommandName => $chainedCommand) {
            if ($chainedCommand->getName() == $commandName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets a main command name that passed command name is chained to.
     *
     * @param string $commandName Command name to get main command for.
     *
     * @return string
     *
     * @throws NotFoundInChainsException|\RuntimeException in case if provided command not present in any chain.
     */
    public function getChainMainCommand($commandName)
    {
        foreach ($this->chains as $mainCommandName => $chainedCommand) {
            if ($chainedCommand->getName() == $commandName) {
                try {
                    return $this->getChainMainCommand($mainCommandName);
                } catch (NotFoundInChainsException $notChainedException) {
                    return $mainCommandName;
                }
            }
        }

        throw new NotFoundInChainsException('Command "' . $commandName . '" were not found in any chain.');
    }

    /**
     * Checking if command already have chain.
     *
     * @param string $commandName
     *
     * @return boolean
     */
    public function hasChainedCommands($commandName)
    {
        return array_key_exists($commandName, $this->chains);
    }

    /**
     * Gets next command in chain by previous commandName.
     *
     * @param string $commandName
     *
     * @return Command
     */
    public function getChainedCommandFor($commandName)
    {
        if (!$this->hasChainedCommands($commandName)) {
            throw new RuntimeException('Command "' . $commandName . '" has no chained commands');
        }

        return $this->chains[$commandName];
    }

    /**
     * Get main command names for all registered chains.
     *
     * @return array
     */
    public function getMainCommandNames()
    {
        $output = [];
        foreach ($this->chains as $mainCommandName => $chainedCommand) {
            if (!$this->isCommandChained($mainCommandName)) {
                $output[] = $mainCommandName;
            }
        }

        return $output;
    }

    /**
     * Gets a chain as array of commands by main chain command name.
     *
     * @param string $commandName
     *
     * @return Command[]
     *
     * @throws RuntimeException|\RuntimeException in case if command have no chain.
     */
    public function getChain($commandName)
    {
        $chain = $this->buildChain($commandName);
        if (count($chain) == 0) {
            throw new RuntimeException('No chained commands registered for "' . $commandName . '"');
        }

        return $chain;
    }

    /**
     * Building chained commands array recursively.
     *
     * @param string $commandName
     *
     * @return array
     */
    protected function buildChain($commandName)
    {
        if ($this->hasChainedCommands($commandName)) {
            $chainedCommand = $this->getChainedCommandFor($commandName);
            $chain = $this->buildChain($chainedCommand->getName());
            array_unshift($chain, $chainedCommand);

            return $chain;
        }

        return [];
    }
}
