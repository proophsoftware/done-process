<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10/16/15 - 8:37 PM
 */
namespace Prooph\Processing\Workflow;

use Prooph\Common\Messaging\Message;
use Prooph\Done\ChapterLogger\ChapterLogger;

/**
 * Class ExecuteStage
 *
 * Implementation detail of Next
 *
 * @package Prooph\Processing\Workflow
 */
final class ExecuteStage
{
    /**
     * @param callable $stage
     * @param Message $chapterCommand
     * @param mixed $data
     * @param ChapterLogger $chapterLogger
     * @param callable $next
     * @return mixed
     */
    public function __invoke(callable $stage, Message $chapterCommand, $data, ChapterLogger $chapterLogger, callable $next)
    {
        return $stage($chapterCommand, $data, $chapterLogger, $next);
    }
}
 