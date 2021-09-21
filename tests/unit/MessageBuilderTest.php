<?php

namespace tests\unit;

use alexeevdv\yii\graylog\behaviors\GenerateNewIdBehavior;
use alexeevdv\yii\graylog\MessageBuilder;
use alexeevdv\yii\graylog\Target;
use Exception;
use yii\base\Security;
use yii\helpers\VarDumper;
use yii\log\Logger;

class MessageBuilderTest extends Unit
{

    public function testBuildSimpleStringMessage()
    {
        $this->mockApplication();

        $target = new Target();

        $builder = new MessageBuilder;
        $gelfMessage = $builder->build($target, [
            'Text message',
            Logger::LEVEL_ERROR,
            'application',
            1552400424,
        ]);

        $messageArray = $gelfMessage->toArray();

        $this->assertArrayHasKey('short_message', $messageArray);
        $this->assertArrayHasKey('level', $messageArray);
        $this->assertArrayHasKey('timestamp', $messageArray);
        $this->assertArrayHasKey('facility', $messageArray);
        $this->assertArrayHasKey('file', $messageArray);
        $this->assertArrayHasKey('line', $messageArray);
        $this->assertArrayHasKey('_category', $messageArray);
        $this->assertArrayNotHasKey('_unique_request_id', $messageArray);

        $this->assertEquals('Text message', $messageArray['short_message']);
        $this->assertEquals(3, $messageArray['level']);
        $this->assertEquals(1552400424.0, $messageArray['timestamp']);
        $this->assertEquals('testapp', $messageArray['facility']);
        $this->assertEquals('unknown', $messageArray['file']);
        $this->assertEquals(0, $messageArray['line']);
        $this->assertEquals('application', $messageArray['_category']);
    }

    public function testBuildExceptionMessage()
    {
        $this->mockApplication();

        $exception = new Exception('Kaboom');

        $target = new Target();

        $builder = new MessageBuilder;
        $gelfMessage = $builder->build($target, [
            $exception,
            Logger::LEVEL_WARNING,
            'application',
            1552400424,
        ]);

        $messageArray = $gelfMessage->toArray();

        $this->assertArrayHasKey('short_message', $messageArray);
        $this->assertArrayHasKey('level', $messageArray);
        $this->assertArrayHasKey('timestamp', $messageArray);
        $this->assertArrayHasKey('facility', $messageArray);
        $this->assertArrayHasKey('file', $messageArray);
        $this->assertArrayHasKey('line', $messageArray);
        $this->assertArrayHasKey('_category', $messageArray);

        $this->assertArrayNotHasKey('_unique_request_id', $messageArray);

        $this->assertEquals('Exception Exception: Kaboom', $messageArray['short_message']);
        $this->assertEquals(4, $messageArray['level']);
        $this->assertEquals(1552400424.0, $messageArray['timestamp']);
        $this->assertEquals('testapp', $messageArray['facility']);
        $this->assertEquals(__FILE__, $messageArray['file']);
        $this->assertEquals(54, $messageArray['line']);
        $this->assertEquals('application', $messageArray['_category']);
    }

    public function testBuildSimpleTextWithStackTrace()
    {
        $this->mockApplication();

        $target = new Target();

        $builder = new MessageBuilder;
        $gelfMessage = $builder->build($target, [
            'Text message',
            Logger::LEVEL_INFO,
            'application',
            1552400424,
            [
                ['file' => 'file1', 'line' => 12],
                ['file' => 'file2', 'line' => 33],
            ]
        ]);

        $messageArray = $gelfMessage->toArray();

        $this->assertArrayNotHasKey('_unique_request_id', $messageArray);

        $this->assertArrayHasKey('short_message', $messageArray);
        $this->assertArrayHasKey('level', $messageArray);
        $this->assertArrayHasKey('timestamp', $messageArray);
        $this->assertArrayHasKey('facility', $messageArray);
        $this->assertArrayHasKey('file', $messageArray);
        $this->assertArrayHasKey('line', $messageArray);
        $this->assertArrayHasKey('_category', $messageArray);

        $this->assertEquals('Text message', $messageArray['short_message']);
        $this->assertEquals(6, $messageArray['level']);
        $this->assertEquals(1552400424.0, $messageArray['timestamp']);
        $this->assertEquals('testapp', $messageArray['facility']);
        $this->assertEquals('file1', $messageArray['file']);
        $this->assertEquals(12, $messageArray['line']);
        $this->assertEquals('application', $messageArray['_category']);
    }

    public function testBuildArray()
    {
        $this->mockApplication();

        $target = new Target();

        $builder = new MessageBuilder;
        $gelfMessage = $builder->build($target, [
            [
                'short' => 'Short message',
                'full' => 'Full message',
                'additional' => [
                    'field1' => 'value1',
                    'field2' => [0, 1, 2]
                ],
            ],
            Logger::LEVEL_TRACE,
            'application',
            1552400424,
        ]);


        $messageArray = $gelfMessage->toArray();

        $this->assertArrayNotHasKey('_unique_request_id', $messageArray);

        $this->assertArrayHasKey('short_message', $messageArray);
        $this->assertArrayHasKey('full_message', $messageArray);
        $this->assertArrayHasKey('level', $messageArray);
        $this->assertArrayHasKey('timestamp', $messageArray);
        $this->assertArrayHasKey('facility', $messageArray);
        $this->assertArrayHasKey('file', $messageArray);
        $this->assertArrayHasKey('line', $messageArray);
        $this->assertArrayHasKey('_category', $messageArray);
        $this->assertArrayHasKey('_field1', $messageArray);
        $this->assertArrayHasKey('_field2', $messageArray);

        $this->assertEquals('Short message', $messageArray['short_message']);
        $this->assertEquals('Full message', $messageArray['full_message']);
        $this->assertEquals(7, $messageArray['level']);
        $this->assertEquals(1552400424.0, $messageArray['timestamp']);
        $this->assertEquals('testapp', $messageArray['facility']);
        $this->assertEquals('unknown', $messageArray['file']);
        $this->assertEquals(0, $messageArray['line']);
        $this->assertEquals('application', $messageArray['_category']);
        $this->assertEquals('value1', $messageArray['_field1']);
        $this->assertEquals([0, 1, 2], $messageArray['_field2']);
    }

    public function testBuildGenericArray()
    {
        $this->mockApplication();

        $target = new Target();

        $builder = new MessageBuilder;
        $gelfMessage = $builder->build($target, [
            [
                'First',
                'Second'
            ],
            Logger::LEVEL_PROFILE,
            'application',
            1552400424,
        ]);

        $messageArray = $gelfMessage->toArray();

        $this->assertArrayNotHasKey('_unique_request_id', $messageArray);

        $this->assertArrayHasKey('version', $messageArray);
        $this->assertArrayHasKey('host', $messageArray);
        $this->assertArrayHasKey('short_message', $messageArray);
        $this->assertArrayHasKey('level', $messageArray);
        $this->assertArrayHasKey('timestamp', $messageArray);
        $this->assertArrayHasKey('facility', $messageArray);
        $this->assertArrayHasKey('file', $messageArray);
        $this->assertArrayHasKey('line', $messageArray);
        $this->assertArrayHasKey('_category', $messageArray);
        $this->assertArrayHasKey('_0', $messageArray);
        $this->assertArrayHasKey('_1', $messageArray);

        
        $this->assertEquals(VarDumper::dumpAsString(['First','Second']), $messageArray['short_message']);
        $this->assertEquals(7, $messageArray['level']);
        $this->assertEquals(1552400424.0, $messageArray['timestamp']);
        $this->assertEquals('testapp', $messageArray['facility']);
        $this->assertEquals('unknown', $messageArray['file']);
        $this->assertEquals(0, $messageArray['line']);
        $this->assertEquals('application', $messageArray['_category']);
        $this->assertEquals('First', $messageArray['_0']);
        $this->assertEquals('Second', $messageArray['_1']);
    }

    public function testAddingUniqueRequestId()
    {
        $this->mockApplication(
            [
                'components' => [
                    'security' => Security::class
                ],
                'as uniqueGenerator' => GenerateNewIdBehavior::class,
            ]
        );

        $target = new Target();

        $builder = new MessageBuilder;
        $gelfMessage = $builder->build($target, [
            'Text message',
            Logger::LEVEL_ERROR,
            'application',
            1552400424,
        ]);

        $messageArray = $gelfMessage->toArray();
    }
}
