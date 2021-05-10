<?php

use app\modules\github\models\forms\UsersForm;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $usersForm  UsersForm */
/* @var $this       View      */

?>

<h1>Update</h1>

<?php $form = ActiveForm::begin(); ?>
    <?php Pjax::begin(); ?>

    <?= $form->field($usersForm, 'users')->textarea(['rows' => 10]) ?>

    <?= Html::submitButton('Сохранить') ?>

    <?php Pjax::end(); ?>
<?php ActiveForm::end(); ?>

<p>Список пользователей, каждый пользователь с новой строки</p>

