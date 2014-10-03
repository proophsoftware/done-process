<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 02.10.14 - 22:09
 */

namespace Ginger\Message\ProophPlugin;

use Prooph\ServiceBus\Message\MessageInterface;

interface ServiceBusTranslatableMessage
{
    /**
     * @param MessageInterface $aMessage
     * @return static
     */
    public static function fromServiceBusMessage(MessageInterface $aMessage);

    /**
     * @return MessageInterface
     */
    public function toServiceBusMessage();
}
 