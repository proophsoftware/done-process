<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10/18/15 - 9:52 PM
 */
namespace Prooph\Done\Process;

use Prooph\Common\Messaging\Message;
use Prooph\Done\ChapterLogger\ChapterLogger;
use Prooph\Done\Process\Type\Prototype;
use Prooph\Done\Process\Type\Type;

/**
 * Interface PrototypeHandlingMiddleware
 *
 * @package Prooph\Done\Process
 */
interface PrototypeHandlingMiddleware 
{
    /**
     * @param Message $chapterCommand
     * @param Prototype $prototype
     * @param ChapterLogger $chapterLogger
     * @param callable $next
     * @return Type the processed data
     */
    public function __invoke(Message $chapterCommand, Prototype $prototype, ChapterLogger $chapterLogger, callable $next);
}
 