<?php

namespace PM\ChainCommandBundle\Tests\Service;

use PM\ChainCommandBundle\Service\ChainCollectionService;

class ChainCollectionServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testAttachCommand()
    {
        $collection = new ChainCollectionService();
        $command = new \Symfony\Component\Console\Command\Command('chained:command');
        $mainCommand = new \Symfony\Component\Console\Command\Command('main:command');

        $collection->attachCommandTo($command, $mainCommand->getName());

        $this->assertEquals($mainCommand->getName(), $collection->getChainMainCommand($command->getName()));
        $this->assertEquals(
            $command,
            $collection->getChainedCommandFor($mainCommand->getName())
        );

        $this->assertEquals(
            [$command],
            $collection->getChain($mainCommand->getName())
        );

        $this->assertTrue($collection->isCommandChained($command->getName()));
        $this->assertFalse($collection->isCommandChained($mainCommand->getName()));

        $this->assertTrue($collection->hasChainedCommands($mainCommand->getName()));
        $this->assertFalse($collection->hasChainedCommands($command->getName()));

        $this->assertEquals(
            [$mainCommand->getName()],
            $collection->getMainCommandNames()
        );


    }

    public function testCanNotAttachToItself()
    {
        $collection = new ChainCollectionService();
        $command = new \Symfony\Component\Console\Command\Command('chained:command');

        $this->setExpectedException(\PM\ChainCommandBundle\Exception\Service\ChainCollectionService\RuntimeException::class);

        $collection->attachCommandTo($command, $command->getName());
    }

    public function testAttachCommandFails()
    {
        $collection = new ChainCollectionService();
        $command = new \Symfony\Component\Console\Command\Command('chained:command');
        $mainCommand = new \Symfony\Component\Console\Command\Command('main:command');

        $this->setExpectedException(\PM\ChainCommandBundle\Exception\Service\ChainCollectionService\RuntimeException::class);

        $collection->attachCommandTo($command, $mainCommand->getName());
        $collection->attachCommandTo($command, $mainCommand->getName());
    }

    public function testGetChainedCommandForFails()
    {
        $collection = new ChainCollectionService();
        $this->setExpectedException(\PM\ChainCommandBundle\Exception\Service\ChainCollectionService\RuntimeException::class);
        $collection->getChainedCommandFor('some:notChained:command');
    }

    public function testGetChainFails()
    {
        $collection = new ChainCollectionService();
        $this->setExpectedException(\PM\ChainCommandBundle\Exception\Service\ChainCollectionService\RuntimeException::class);
        $collection->getChain('some:notChained:command');
    }
}
