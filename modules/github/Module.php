<?php

namespace app\modules\github;

use Yii;
use yii\console\Application;

/**
 * github module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\github\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof Application) {
            $this->controllerNamespace = 'app\modules\github\commands';
        }
    }
}