<?php

namespace tests\unit\behaviors;

use alexeevdv\yii\graylog\behaviors\GenerateNewIdBehavior;
use Codeception\Test\Unit;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\Security;
use yii\console\Application;

class GenerateNewIdBehaviorTest extends Unit
{
    public function testGettingId()
    {
        $app = new Application([
            'id' => 'test',
            'basePath' => __DIR__,
        ]);

        $behavior = new GenerateNewIdBehavior([
            'security' => $this->make(Security::class, [
                'generateRandomString' => 'test'
            ])
        ]);

        $app->attachBehavior('test', $behavior);

        $this->assertEquals('test', $app->uniqueRequestId);
        $this->assertEquals('test', $app->getUniqueRequestId());
    }

    public function testAttachingToNotApplication()
    {
        $object = new Component();
        
        $behavior = new GenerateNewIdBehavior([
            'security' => $this->make(Security::class, [
                'generateRandomString' => 'test'
            ])
        ]);
            
        $this->expectExceptionMessage('Behavior `owner` has to be `[[yii\base\Module]]`');
        $this->expectException(InvalidConfigException::class);
        $object->attachBehavior('test', $behavior);
    }
}
