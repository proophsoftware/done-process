<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10/16/15 - 9:17 PM
 */
namespace ProophTest\Processing;

use Prooph\Common\Messaging\Message;
use Prooph\Done\ChapterLogger\ChapterLogger;
use Prooph\Done\ChapterLogger\DoneBackend;
use Prooph\Done\Shared\Metadata;
use Prooph\Processing\Functional\Func;
use Prooph\Processing\Workflow;
use ProophTest\Processing\Mock\AddressDictionary;
use ProophTest\Processing\Mock\UserDictionary;
use Rhumsaa\Uuid\Uuid;

/**
 * Class WorkflowTest
 *
 * @package ProophTest\Processing
 */
final class WorkflowTest extends TestCase
{
    /**
     * @test
     */
    public function it_pipes_middleware_and_executes_the_pipeline()
    {
        $chapterCommand = $this->prophesize(Message::class);

        $chapterCommand->metadata()->willReturn([
            Metadata::STORY_CHAPTER => Uuid::uuid4()->toString(). '____1',
            Metadata::STORY_NAME => 'Test Story'
        ]);

        $chapterCommand->messageName()->willReturn('Chapter Command');

        $chapterCommand->uuid()->willReturn(Uuid::uuid4());

        $doneBackend    = $this->prophesize(DoneBackend::class);
        $chapterLogger  = ChapterLogger::fromChapterCommand($chapterCommand->reveal(), $doneBackend->reveal());

        $workflow = new Workflow();

        $workflow->pipe(function(Message $chapterCommand, array $userData, ChapterLogger $chapterLogger, callable $next) {
            $user = UserDictionary::fromNativeValue($userData);

            return $next($chapterCommand, $user, $chapterLogger);
        });

        $workflow->pipe(function(Message $chapterCommand, UserDictionary $user, ChapterLogger $chapterLogger, callable $next) {
            return $next($chapterCommand, $user->property('address')->type(), $chapterLogger);
        });

        $userData = [
            'id' => 1,
            'name' => 'Alex',
            'address' => [
                'street' => 'Main Street',
                'streetNumber' => 10,
                'zip' => '12345',
                'city' => 'Test City'
            ]
        ];

        $addressData = $workflow(
            $chapterCommand->reveal(),
            $userData,
            $chapterLogger,
            function(Message $chapterCommand, AddressDictionary $address, ChapterLogger $chapterLogger) {
                return Func::to_data($address);
            }
        );

        $this->assertEquals([
            'street' => 'Main Street',
            'streetNumber' => 10,
            'zip' => '12345',
            'city' => 'Test City'
        ], $addressData);
    }
}
