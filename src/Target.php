<?php

namespace alexeevdv\yii\graylog;

use Gelf\PublisherInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\Instance;

class Target extends \yii\log\Target
{
    /**
     * @var string
     */
    public $facility;

    /**
     * @var PublisherInterface|array|string
     */
    public $publisher = Publisher::class;

    /**
     * @var MessageBuilderInterface|array|string
     */
    public $messageBuilder = MessageBuilder::class;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->facility === null) {
            $this->facility = Yii::$app->id;
        }
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidConfigException
     */
    public function export()
    {
        /** @var PublisherInterface $publisher */
        $publisher = Instance::ensure($this->publisher, PublisherInterface::class);

        /** @var MessageBuilderInterface $messageBuilder */
        $messageBuilder = Instance::ensure($this->messageBuilder, MessageBuilderInterface::class);

        foreach ($this->messages as $message) {
            $gelfMessage = $messageBuilder->build($this, $message);
            $publisher->publish($gelfMessage);
        }
    }
}
