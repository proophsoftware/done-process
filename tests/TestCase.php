<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.07.14 - 21:33
 */

namespace ProophTest\Processing;

use Prooph\Processing\Message\MessageNameUtils;
use Prooph\Processing\Message\ProophPlugin\FromProcessingMessageTranslator;
use Prooph\Processing\Message\ProophPlugin\HandleWorkflowMessageInvokeStrategy;
use Prooph\Processing\Message\ProophPlugin\ToProcessingMessageTranslator;
use Prooph\Processing\Message\WorkflowMessage;
use Prooph\Processing\Processor\Command\StartSubProcess;
use Prooph\Processing\Processor\Definition;
use Prooph\Processing\Processor\NodeName;
use Prooph\Processing\Processor\ProcessFactory;
use Prooph\Processing\Processor\ProcessRepository;
use Prooph\Processing\Processor\ProophPlugin\SingleTargetMessageRouter;
use Prooph\Processing\Processor\ProophPlugin\WorkflowProcessorInvokeStrategy;
use Prooph\Processing\Processor\RegistryWorkflowEngine;
use Prooph\Processing\Processor\WorkflowProcessor;
use ProophTest\Processing\Mock\TestWorkflowMessageHandler;
use ProophTest\Processing\Mock\UserDictionary;
use Prooph\EventStore\Adapter\InMemoryAdapter;
use Prooph\EventStore\Configuration\Configuration;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\PersistenceEvent\PostCommitEvent;
use Prooph\EventStore\Stream\Stream;
use Prooph\EventStore\Stream\StreamName;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\InvokeStrategy\ForwardToRemoteMessageDispatcherStrategy;
use Prooph\ServiceBus\Message\InMemoryRemoteMessageDispatcher;
use Prooph\ServiceBus\Router\CommandRouter;
use Prooph\ServiceBus\Router\EventRouter;

/**
 * Class TestCase
 *
 * @package ProophTest\Processing
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class TestCase extends \PHPUnit_Framework_TestCase
{

}
 