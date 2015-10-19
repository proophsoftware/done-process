<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10/19/15 - 6:02 PM
 */
namespace Prooph\Done\Process;

use Prooph\Common\Messaging\Message;
use Prooph\Done\ChapterLogger\ChapterLogger;

/**
 * Class ExceptionLogger
 *
 * The ExceptionLogger should be the first middleware in any DONE! workflow.
 *
 * It wraps the process with a try catch and logs a caught exception as error event.
 *
 * @package Prooph\Done\Process
 */
final class ExceptionLogger 
{
    public function __invoke(Message $chapterCommand, $data, ChapterLogger $chapterLogger, callable $next)
    {
        try {
            return $next($chapterCommand, $data, $chapterLogger);
        } catch (\Exception $ex) {
            $chapterLogger->error($ex->getMessage(), [
                'middleware' => __CLASS__,
                'chapter_command' => $chapterCommand->messageName(),
                'chapter_command_uuid' => $chapterCommand->uuid()->toString(),
                'exception_code' => $ex->getCode(),
                'trace' => (string)$ex
            ]);
            return;
        }
    }
}
 