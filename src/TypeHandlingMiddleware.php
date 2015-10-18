<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10/16/15 - 7:57 PM
 */

namespace Prooph\Done\Process;

use Prooph\Common\Messaging\Message;
use Prooph\Done\ChapterLogger\ChapterLogger;
use Prooph\Done\Process\Type\Type;

/**
 * Interface TypeHandlingMiddleware
 *
 * @package Prooph\Done\Process
 */
interface TypeHandlingMiddleware
{
    /**
     * @param Message $chapterCommand
     * @param Type $data
     * @param ChapterLogger $chapterLogger
     * @param callable $next
     * @return Type|null the processed data or null if processing was not possible
     */
    public function __invoke(Message $chapterCommand, Type $data, ChapterLogger $chapterLogger, callable $next);
}
 