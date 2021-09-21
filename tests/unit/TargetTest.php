<?php

namespace tests\unit;

use alexeevdv\yii\graylog\MessageBuilderInterface;
use alexeevdv\yii\graylog\Target;
use Codeception\Stub\Expected;
use Gelf\MessageInterface;
use Gelf\PublisherInterface;
use yii\log\Logger;

class TargetTest extends Unit
{
    public function testExport()
    {
        $this->mockApplication();

        $target = new Target([
            'publisher' => $this->makeEmpty(PublisherInterface::class, [
                'publish' => Expected::once(),
            ]),
            'messageBuilder' => $this->makeEmpty(MessageBuilderInterface::class, [
                'build' => Expected::once($this->makeEmpty(MessageInterface::class)),
            ]),
            'messages' => [
                ['Short text', Logger::LEVEL_TRACE, 'application', 1552400424],
            ],
        ]);
        $target->export();
    }
}
