<?php

/* @var $data   array           */
/* @var $this   yii\web\View    */

use yii\helpers\Html;

$this->title = 'My Yii Application';
?>

<p><strong>Обновлено <?= $data['updated_at'] ?></strong></p>
<ul>
    <?php foreach ($data['result'] as $repository): ?>
        <li>
            <?= Html::a(
                $repository['name'],
                $repository['link'],
                [
                    'target' => '_blank',
                ]
            ) ?>
        </li>
    <?php endforeach; ?>
</ul>
