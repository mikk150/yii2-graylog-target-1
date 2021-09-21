<?php

namespace alexeevdv\yii\graylog\behaviors;

use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\base\Security;
use yii\di\Instance;

class GenerateNewIdBehavior extends Behavior
{
    /**
     * @var Security|array|string
     */
    public $security = 'security';

    /**
     * @var Module
     */
    public $owner;

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        parent::attach($owner);
        if (!($this->owner instanceof Module)) {
            throw new InvalidConfigException('Behavior `owner` has to be `[[yii\base\Module]]`');
        }

        $this->security = Instance::ensure($this->security, Security::class, $this->owner);
    }

    private $_uniqueRequestId;

    /**
     * @return void 
     */
    public function getUniqueRequestId()
    {
        if (!$this->_uniqueRequestId) {
            $this->_uniqueRequestId = $this->security->generateRandomString();
        }

        return $this->_uniqueRequestId;
    }
}
