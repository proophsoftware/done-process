<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10/16/15 - 8:29 PM
 */
namespace Prooph\Processing\Workflow;

use Prooph\Common\Messaging\Message;
use Prooph\Done\ChapterLogger\ChapterLogger;

/**
 * Class Next
 *
 * Implementation detail of the Workflow pipeline
 *
 * @package Prooph\Processing\Workflow
 */
final class Next
{
    /**
     * @var \SplQueue
     */
    private $queue;

    /**
     * @var callable
     */
    private $done;

    /**
     * @var ExecuteStage
     */
    private $executeStage;

    /**
     * @param \SplQueue $queue
     * @param callable $done
     */
    public function __construct(\SplQueue $queue, callable $done)
    {
        $this->queue = clone $queue;
        $this->done  = $done;
        $this->executeStage = new ExecuteStage();
    }

    /**
     * @param Message $chapterCommand
     * @param mixed $data
     * @param ChapterLogger $chapterLogger
     */
    public function __invoke(Message $chapterCommand, $data, ChapterLogger $chapterLogger)
    {
        $done = $this->done;

        // No middleware remains; done
        if ($this->queue->isEmpty()) {
            return $done($chapterCommand, $data, $chapterLogger);
        }

        $stage = $this->queue->dequeue();
        $executeStage = $this->executeStage;

        return $executeStage($stage, $chapterCommand, $data, $chapterLogger, $this);
    }
}