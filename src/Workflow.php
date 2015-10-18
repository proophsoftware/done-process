<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10/16/15 - 7:48 PM
 */
namespace Prooph\Done\Process;

use Prooph\Common\Messaging\Message;
use Prooph\Done\ChapterLogger\ChapterLogger;
use Prooph\Done\Process\Workflow\Next;

/**
 * Class Workflow
 *
 * Pipeline implementation specifically designed to handle proophsoftware/done chapter commands.
 *
 * @package Prooph\Done\Process
 */
final class Workflow 
{
    /**
     * @var \SplQueue
     */
    private $pipeline;

    public function __construct()
    {
        $this->pipeline = new \SplQueue();
    }

    /**
     * @param callable $middleware
     */
    public function pipe(callable $middleware)
    {
        $this->pipeline->enqueue($middleware);
    }

    /**
     * @param Message $chapterCommand
     * @param mixed $data
     * @param ChapterLogger $chapterLogger
     * @param callable $done
     * @return mixed
     */
    public function __invoke(Message $chapterCommand, $data, ChapterLogger $chapterLogger, callable $done = null)
    {
        $done = $done ?: function (Message $chapterCommand, $data, ChapterLogger $chapterLogger) { return $data; };

        $next = new Next($this->pipeline, $done);

        return $next($chapterCommand, $data, $chapterLogger);
    }
}
 