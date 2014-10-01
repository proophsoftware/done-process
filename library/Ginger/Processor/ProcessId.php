<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 30.09.14 - 21:48
 */

namespace Ginger\Processor;

use Rhumsaa\Uuid\Uuid;

/**
 * Class ProcessId
 *
 * @package Ginger\Processor
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class ProcessId 
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @return ProcessId
     */
    public static function generate()
    {
        return new self(Uuid::uuid4());
    }

    /**
     * @param $uuid
     * @return ProcessId
     */
    public static function reconstituteFromString($uuid)
    {
        return new self(Uuid::fromString($uuid));
    }

    /**
     * @param Uuid $uuid
     */
    private function __construct(Uuid $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->uuid->toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @param ProcessId $processId
     * @return bool
     */
    public function equals(ProcessId $processId)
    {
        return $this->uuid->equals($processId->uuid);
    }
}
 