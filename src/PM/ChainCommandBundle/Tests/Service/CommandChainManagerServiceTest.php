<?php

namespace PM\ChainCommandBundle\Tests\Service;

use PM\ChainCommandBundle\Exception\Service\CommandChainManager\RuntimeException;
use PM\ChainCommandBundle\Service\ChainCollectionService;
use PM\ChainCommandBundle\Service\CommandChainManagerService;
use PM\ChainCommandBundle\Tests\CommandFixture\FixtureCommandChained;
use PM\ChainCommandBundle\Tests\CommandFixture\FixtureCommandMain;
use PM\ChainCommandBundle\Tests\CommandFixture\FixtureCommandNonZeroExit;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Ldap\Adapter\CollectionInterface;

class CommandChainManagerServiceTest extends KernelTestCase
{
    /**
     * @var
     */
    private $container;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcherMock;

    /**
     * @var CollectionInterface
     */
    private $collection;

    /**
     * @{inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();

        $this->container = self::$kernel->getContainer();
        $eventDispatcher = $this->container->get('event_dispatcher');
        $this->eventDispatcherMock = $this->getMockBuilder(get_class($eventDispatcher))->disableOriginalConstructor()->getMock();

        $this->collection = new ChainCollectionService();
    }

    /**
     *  Testing ability to add command to chain.
     */
    public function testAddCommandToChain()
    {
        $manager = new CommandChainManagerService($this->collection, $this->eventDispatcherMock);

        $command = new Command('chained:command');
        $mainCommand = new Command('main:command');

        $manager->addCommandToChain($mainCommand->getName(), $command);

        $this->assertTrue($manager->hasChainedCommands($mainCommand->getName()));
        $this->assertFalse($manager->hasChainedCommands($command->getName()));

        $this->assertTrue($manager->isCommandChained($command->getName()));
        $this->assertFalse($manager->isCommandChained($mainCommand->getName()));

        $this->assertEquals($manager->getChainMainCommand($command->getName()), $mainCommand->getName());

        $this->assertEquals(
            [$command->getName()],
            $manager->getCommandNameChain($mainCommand->getName())
        );

        $this->assertEquals(
            [$mainCommand->getName() => [$command->getName()]],
            $manager->getCommandNameChains()
        );
    }

    /**
     * Testing ability to run chain.
     *
     */
    public function testRunChain()
    {
        $manager = new CommandChainManagerService($this->collection, $this->eventDispatcherMock);

        $main = new FixtureCommandMain();
        $chained = new FixtureCommandChained();

        $manager->addCommandToChain($main->getName(), $chained);

        $input = new ArgvInput();
        $output = new BufferedOutput();
        $exitCode = $manager->runChain($main, $input, $output);
        $this->assertEquals(0, $exitCode);
        $this->assertEquals("main\nchained\n", $output->fetch());
    }

    /**
     * Testing run chain fail case.
     */
    public function testRunChainFilByMainCommand()
    {
        $main = new FixtureCommandNonZeroExit();
        $chained = new FixtureCommandChained();
        $manager = new CommandChainManagerService($this->collection, $this->eventDispatcherMock);
        $manager->addCommandToChain($main->getName(), $chained);

        $input = new ArgvInput();
        $output = new BufferedOutput();
        $this->setExpectedException(\PM\ChainCommandBundle\Exception\Service\CommandChainManager\RuntimeException::class);
        $manager->runChain($main, $input, $output);
    }

    public function testRunChainFilByChainedCommand()
    {
        $manager = new CommandChainManagerService($this->collection, $this->eventDispatcherMock);

        $main = new FixtureCommandMain();
        $chained = new FixtureCommandNonZeroExit();

        $manager->addCommandToChain($main->getName(), $chained);

        $input = new ArgvInput();
        $output = new BufferedOutput();
        $this->setExpectedException(\PM\ChainCommandBundle\Exception\Service\CommandChainManager\RuntimeException::class);
        $manager->runChain($main, $input, $output);
    }

    /**
     * Testing add to chain fail case.
     */
    public function testAddCommandToChainHaveChainFail()
    {
        $manager = new CommandChainManagerService($this->collection, $this->eventDispatcherMock);

        $command = new Command('chained:command');
        $command2 = new Command('chained:other:command');
        $mainCommand = new Command('main:command');

        $manager->addCommandToChain($mainCommand->getName(), $command);
        $this->setExpectedException(RuntimeException::class);

        $manager->addCommandToChain($mainCommand->getName(), $command2);
    }

    /**
     * Testing add to chain fail case.
     */
    public function testAddCommandToChainAlreadyChainedFail()
    {
        $manager = new CommandChainManagerService($this->collection, $this->eventDispatcherMock);

        $command = new Command('chained:command');
        $command2 = new Command('chained:other:command');
        $mainCommand = new Command('main:command');

        $manager->addCommandToChain($mainCommand->getName(), $command);

        $this->setExpectedException(RuntimeException::class);

        $manager->addCommandToChain($command2->getName(), $command);
    }
}
