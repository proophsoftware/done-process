<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 25.09.14 - 18:37
 */

namespace Ginger\Message\ProophPlugin;

use Ginger\Message\WorkflowMessage;
use Ginger\Processor\Command\StartSubProcess;
use Ginger\Processor\Event\SubProcessFinished;
use Prooph\ServiceBus\Message\MessageInterface;
use Prooph\ServiceBus\Message\ToMessageTranslator;
use Prooph\ServiceBus\Message\ToMessageTranslatorInterface;

/**
 * Class FromGingerMessageTranslator
 *
 * @package Ginger\Message\ProophPlugin
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class FromGingerMessageTranslator implements ToMessageTranslatorInterface
{
    /**
     * @var ToMessageTranslator
     */
    private $psbToMessageTranslator;

    /**
     * @param $aCommandOrEvent
     * @return bool
     */
    public function canTranslateToMessage($aCommandOrEvent)
    {
        return $aCommandOrEvent instanceof ServiceBusTranslatableMessage
            || $aCommandOrEvent instanceof StartSubProcess
            || $aCommandOrEvent instanceof SubProcessFinished;
    }

    /**
     * @param WorkflowMessage $aCommandOrEvent
     * @throws \RuntimeException
     * @return MessageInterface
     */
    public function translateToMessage($aCommandOrEvent)
    {
        if ($aCommandOrEvent instanceof ServiceBusTranslatableMessage)
        {
            return $aCommandOrEvent->toServiceBusMessage();
        }

        if ($aCommandOrEvent instanceof StartSubProcess || $aCommandOrEvent instanceof SubProcessFinished) {
            return $this->getPSBToMessageTranslator()->translateToMessage($aCommandOrEvent);
        }
    }

    /**
     * @return ToMessageTranslator
     */
    private function getPSBToMessageTranslator()
    {
        if (is_null($this->psbToMessageTranslator)) {
            $this->psbToMessageTranslator = new ToMessageTranslator();
        }

        return $this->psbToMessageTranslator;
    }

}
 