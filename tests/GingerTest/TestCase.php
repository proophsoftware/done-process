<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.07.14 - 21:33
 */

namespace GingerTest;

use Ginger\Message\LogMessage;
use Ginger\Message\MessageNameUtils;
use Ginger\Message\ProophPlugin\FromGingerMessageTranslator;
use Ginger\Message\ProophPlugin\HandleWorkflowMessageInvokeStrategy;
use Ginger\Message\ProophPlugin\ToGingerMessageTranslator;
use Ginger\Message\WorkflowMessage;
use Ginger\Processor\Command\StartSubProcess;
use Ginger\Processor\Definition;
use Ginger\Processor\Event\SubProcessFinished;
use Ginger\Processor\NodeName;
use Ginger\Processor\ProcessFactory;
use Ginger\Processor\ProcessRepository;
use Ginger\Processor\ProophPlugin\SingleTargetMessageRouter;
use Ginger\Processor\ProophPlugin\WorkflowProcessorInvokeStrategy;
use Ginger\Processor\RegistryWorkflowEngine;
use Ginger\Processor\WorkflowProcessor;
use GingerTest\Mock\TestWorkflowMessageHandler;
use GingerTest\Mock\UserDictionary;
use Prooph\EventStore\Adapter\InMemoryAdapter;
use Prooph\EventStore\Configuration\Configuration;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\PersistenceEvent\PostCommitEvent;
use Prooph\EventStore\Stream\AggregateStreamStrategy;
use Prooph\EventStore\Stream\Stream;
use Prooph\EventStore\Stream\StreamName;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\InvokeStrategy\ForwardToMessageDispatcherStrategy;
use Prooph\ServiceBus\Message\FromMessageTranslator;
use Prooph\ServiceBus\Message\InMemoryMessageDispatcher;
use Prooph\ServiceBus\Message\ToMessageTranslator;
use Prooph\ServiceBus\Router\CommandRouter;
use Prooph\ServiceBus\Router\EventRouter;

/**
 * Class TestCase
 *
 * This is the base class for all GingerTests. It provides a lot of test objects which are used in the various
 * test cases. It also defines test workflow scenarios and set up the involved components to support them.
 *
 * 1. Scenario:
 *   - Start new LinearMessagingProcess when GingerTest\Mock\UserDictionary was collected from test-case source.
 *     - Use TestCase::getUserDataCollectedTestMessage method to get a ready to use WorkflowMessage
 *   - Send a ProcessData message to a TestWorkflowMessageHandler
 *     - GingerType changes to GingerTest\Mock\TargetUserDictionary
 *
 * @package GingerTest
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RegistryWorkflowEngine
     */
    protected $workflowEngine;

    /**
     * @var RegistryWorkflowEngine
     */
    protected $otherMachineWorkflowEngine;

    /**
     * @var TestWorkflowMessageHandler
     */
    protected $workflowMessageHandler;

    /**
     * @var TestWorkflowMessageHandler
     */
    protected $otherMachineWorkflowMessageHandler;

    /**
     * @var CommandRouter
     */
    protected $commandRouter;

    /**
     * @var CommandRouter
     */
    protected $otherMachineCommandRouter;

    /**
     * @var WorkflowProcessor
     */
    private $workflowProcessor;

    /**
     * @var WorkflowProcessor
     */
    private $otherMachineWorkflowProcessor;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var EventStore
     */
    private $otherMachineEventStore;

    /**
     * @var ProcessRepository
     */
    private $processRepository;

    /**
     * @var ProcessRepository
     */
    private $otherMachineProcessRepository;

    /**
     * @var InMemoryMessageDispatcher
     */
    private $otherMachineMessageDispatcher;

    /**
     * @var PostCommitEvent
     */
    protected $lastPostCommitEvent;

    /**
     * @var PostCommitEvent
     */
    protected $otherMachineLastPostCommitEvent;

    protected $eventNameLog = array();

    protected $otherMachineEventNameLog = array();

    protected function setUp()
    {
        $this->workflowMessageHandler = new TestWorkflowMessageHandler();

        $localCommandBus = new CommandBus();

        $this->commandRouter = new CommandRouter();

        $this->commandRouter->route(MessageNameUtils::getCollectDataCommandName('GingerTest\Mock\UserDictionary'))
            ->to($this->workflowMessageHandler);

        $this->commandRouter->route(MessageNameUtils::getProcessDataCommandName('GingerTest\Mock\TargetUserDictionary'))
            ->to($this->workflowMessageHandler);

        $localCommandBus->utilize($this->commandRouter);

        $localCommandBus->utilize(new HandleWorkflowMessageInvokeStrategy());

        $localCommandBus->utilize(new WorkflowProcessorInvokeStrategy());

        $this->workflowEngine = new RegistryWorkflowEngine();

        $this->workflowEngine->registerCommandBus($localCommandBus, ['test-case', 'test-target', Definition::DEFAULT_NODE_NAME]);
    }

    protected function setUpOtherMachine()
    {
        $this->otherMachineWorkflowMessageHandler = new TestWorkflowMessageHandler();

        $commandBus = new CommandBus();

        $this->otherMachineCommandRouter = new CommandRouter();

        $this->otherMachineCommandRouter->route(MessageNameUtils::getCollectDataCommandName('GingerTest\Mock\UserDictionaryS2'))
            ->to($this->otherMachineWorkflowMessageHandler);

        $this->otherMachineCommandRouter->route(MessageNameUtils::getProcessDataCommandName('GingerTest\Mock\TargetUserDictionary'))
            ->to($this->otherMachineWorkflowMessageHandler);

        $commandBus->utilize($this->otherMachineCommandRouter);

        $commandBus->utilize(new HandleWorkflowMessageInvokeStrategy());

        $commandBus->utilize(new WorkflowProcessorInvokeStrategy());

        $commandBus->utilize(new ToGingerMessageTranslator());

        $this->otherMachineWorkflowEngine = new RegistryWorkflowEngine();

        $this->otherMachineWorkflowEngine->registerCommandBus($commandBus, ['test-case', 'test-target', 'other_machine']);

        //Add second command bus to local workflow engine to forward StartSubProcess command to message dispatcher
        $this->otherMachineMessageDispatcher = new InMemoryMessageDispatcher($commandBus, new EventBus());

        $parentNodeCommandBus = new CommandBus();

        $parentCommandRouter = new CommandRouter();

        $parentCommandRouter->route(StartSubProcess::MSG_NAME)->to($this->otherMachineMessageDispatcher);

        $parentNodeCommandBus->utilize($parentCommandRouter);

        $parentNodeCommandBus->utilize(new ForwardToMessageDispatcherStrategy(new ToMessageTranslator()));

        $this->workflowEngine->registerCommandBus($parentNodeCommandBus, ['other_machine']);

        $this->getOtherMachineWorkflowProcessor();

        //Add event buses to handle SubProcessFinished event
        $parentNodeEventBus = new EventBus();

        $parentNodeEventRouter = new EventRouter();

        $parentNodeEventBus->utilize(new SingleTargetMessageRouter($this->getTestWorkflowProcessor()));

        $parentNodeEventBus->utilize(new ToGingerMessageTranslator());

        $parentNodeEventBus->utilize(new WorkflowProcessorInvokeStrategy());

        $otherMachineEventBus = new EventBus();

        $toParentNodeMessageDispatcher = new InMemoryMessageDispatcher(new CommandBus(), $parentNodeEventBus);

        $otherMachineEventBus->utilize(new SingleTargetMessageRouter($toParentNodeMessageDispatcher));

        $otherMachineEventBus->utilize(new ForwardToMessageDispatcherStrategy(new FromGingerMessageTranslator()));

        $this->otherMachineWorkflowEngine->registerEventBus($otherMachineEventBus, [Definition::DEFAULT_NODE_NAME]);
    }

    protected function tearDown()
    {
        $this->workflowMessageHandler->reset();

        $this->eventStore = null;
        $this->processRepository = null;
        $this->workflowProcessor = null;
        $this->lastPostCommitEvent = null;
        $this->eventNameLog = [];

        if (! is_null($this->otherMachineEventStore)) {
            $this->otherMachineWorkflowMessageHandler->reset();
            $this->otherMachineEventStore = null;
            $this->otherMachineWorkflowProcessor = null;
            $this->otherMachineLastPostCommitEvent = null;
            $this->otherMachineEventNameLog = [];
        }
    }

    /**
     * @return WorkflowMessage
     */
    protected function getUserDataCollectedTestMessage()
    {
        $userData = array(
            'id' => 1,
            'name' => 'Alex',
            'address' => array(
                'street' => 'Main Street',
                'streetNumber' => 10,
                'zip' => '12345',
                'city' => 'Test City'
            )
        );

        $user = UserDictionary::fromNativeValue($userData);

        return WorkflowMessage::newDataCollected($user);
    }

    /**
     * @return WorkflowProcessor
     */
    protected function getTestWorkflowProcessor()
    {
        if (is_null($this->workflowProcessor)) {
            $this->workflowProcessor = new WorkflowProcessor(
                NodeName::defaultName(),
                $this->getTestEventStore(),
                $this->getTestProcessRepository(),
                $this->workflowEngine,
                $this->getTestProcessFactory()
            );
        }

        return $this->workflowProcessor;
    }

    /**
     * @return WorkflowProcessor
     */
    protected function getOtherMachineWorkflowProcessor()
    {
        if (is_null($this->otherMachineWorkflowProcessor)) {
            $this->otherMachineWorkflowProcessor = new WorkflowProcessor(
                NodeName::fromString('other_machine'),
                $this->getOtherMachineEventStore(),
                $this->getOtherMachineProcessRepository(),
                $this->otherMachineWorkflowEngine,
                $this->getTestProcessFactory()
            );
            $this->otherMachineCommandRouter->route(StartSubProcess::MSG_NAME)->to($this->otherMachineWorkflowProcessor);
        }

        return $this->otherMachineWorkflowProcessor;
    }

    /**
     * @return EventStore
     */
    protected function getTestEventStore()
    {
        if (is_null($this->eventStore)) {
            $inMemoryAdapter = new InMemoryAdapter();

            $config = new Configuration();

            $config->setAdapter($inMemoryAdapter);

            $this->eventStore = new EventStore($config);

            $this->eventStore->getPersistenceEvents()->attach("commit.post", function(PostCommitEvent $postCommitEvent) {
                $this->lastPostCommitEvent = $postCommitEvent;

                foreach ($postCommitEvent->getRecordedEvents() as $event) {
                    $this->eventNameLog[] = $event->eventName()->toString();
                }
            });
        }

        return $this->eventStore;
    }

    /**
     * @return EventStore
     */
    protected function getOtherMachineEventStore()
    {
        if (is_null($this->otherMachineEventStore)) {
            $inMemoryAdapter = new InMemoryAdapter();

            $config = new Configuration();

            $config->setAdapter($inMemoryAdapter);

            $this->otherMachineEventStore = new EventStore($config);

            $this->otherMachineEventStore->getPersistenceEvents()->attach("commit.post", function(PostCommitEvent $postCommitEvent) {
                $this->otherMachineLastPostCommitEvent = $postCommitEvent;

                foreach ($postCommitEvent->getRecordedEvents() as $event) {
                    $this->otherMachineEventNameLog[] = $event->eventName()->toString();
                }
            });
        }

        return $this->otherMachineEventStore;
    }

    /**
     * @return ProcessRepository
     */
    protected function getTestProcessRepository()
    {
        if (is_null($this->processRepository)) {
            $this->processRepository = new ProcessRepository($this->getTestEventStore());

            $this->getTestEventStore()->beginTransaction();

            $this->getTestEventStore()->create(new Stream(new StreamName('Ginger\Processor\Process'), []));

            $this->getTestEventStore()->commit();
        }

        return $this->processRepository;
    }

    /**
     * @return ProcessRepository
     */
    protected function getOtherMachineProcessRepository()
    {
        if (is_null($this->otherMachineProcessRepository)) {
            $this->otherMachineProcessRepository = new ProcessRepository($this->getOtherMachineEventStore());

            $this->getOtherMachineEventStore()->beginTransaction();

            $this->getOtherMachineEventStore()->create(new Stream(new StreamName('Ginger\Processor\Process'), []));

            $this->getOtherMachineEventStore()->commit();
        }

        return $this->otherMachineProcessRepository;
    }

    /**
     * @return ProcessFactory
     */
    private function getTestProcessFactory()
    {
        $processDefinitionS1 = [
            "process_type" => Definition::PROCESS_LINEAR_MESSAGING,
            "tasks" => [
                [
                    "task_type"      => Definition::TASK_PROCESS_DATA,
                    "target"         => 'test-target',
                    "allowed_types"  => ['GingerTest\Mock\TargetUserDictionary']
                ]
            ]
        ];

        $processDefinitionS2 = [
            "process_type" => Definition::PROCESS_LINEAR_MESSAGING,
            "tasks" => [
                [
                    "task_type"          => Definition::TASK_RUN_SUB_PROCESS,
                    "target_node_name"   => 'other_machine',
                    "process_definition" => [
                        "process_type" => Definition::PROCESS_LINEAR_MESSAGING,
                        "tasks" => [
                            [
                                "task_type"      => Definition::TASK_PROCESS_DATA,
                                "target"         => 'test-target',
                                "allowed_types"  => ['GingerTest\Mock\TargetUserDictionary']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $wfMessage = $this->getUserDataCollectedTestMessage();

        $scenario2Message = clone $wfMessage;

        $scenario2Message->changeGingerType('GingerTest\Mock\UserDictionaryS2');

        return new ProcessFactory(
            [
                //Scenario 1 definition
                $wfMessage->getMessageName() => $processDefinitionS1,
                //Scenario 2 definition
                $scenario2Message->getMessageName() => $processDefinitionS2
            ]
        );
    }
}
 