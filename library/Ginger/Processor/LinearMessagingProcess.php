<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 04.10.14 - 20:14
 */

namespace Ginger\Processor;

use Ginger\Message\WorkflowMessage;
use Ginger\Message\LogMessage;
use Ginger\Processor\Task\CollectData;
use Ginger\Processor\Task\Event\LogMessageReceived;
use Ginger\Processor\Task\Event\TaskEntryMarkedAsFailed;
use Ginger\Processor\Task\Event\TaskEntryMarkedAsRunning;

/**
 * Class LinearMessagingProcess
 *
 * @package Ginger\Processor
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class LinearMessagingProcess extends AbstractMessagingProcess
{
    /**
     * Start or continue the process with the help of given WorkflowEngine and optionally with given WorkflowMessage
     *
     * @param WorkflowEngine $workflowEngine
     * @param WorkflowMessage $workflowMessage
     * @return void
     */
    public function perform(WorkflowEngine $workflowEngine, WorkflowMessage $workflowMessage = null)
    {
        $taskListEntry = $this->taskList->getNextNotStartedTaskListEntry();

        if ($taskListEntry) {

            $this->recordThat(TaskEntryMarkedAsRunning::at($taskListEntry->taskListPosition()));

            $task = $taskListEntry->task();

            //Start process with a CollectData task if one is set up
            if (is_null($workflowMessage)) {
                if (! $task instanceof CollectData) {
                    $this->receiveMessage(LogMessage::logNoMessageReceivedFor($task, $taskListEntry->taskListPosition()));

                    if (! $this->config->booleanValue('stop_on_error')) {
                        $this->perform($workflowEngine);
                        return;
                    }
                }

                $this->performCollectData($task, $taskListEntry->taskListPosition(), $workflowEngine);
                return;
            }
        }
    }


}
 