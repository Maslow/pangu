<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $form \yii\widgets\ActiveForm */
/* @var $model \app\modules\man\models\CreateForm */

$this->title = Yii::t('man', 'Create Manager');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
    </div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'id' => 'create-manager',
            'options' => ['class' => 'form-horizontal'],
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"col-sm-3\">{input}</div>\n<div class=\"col-sm-7\">{error}</div>",
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
            ]
        ]); ?>
        <?= $form->field($model, 'username') ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
        <?= $form->field($model, 'password_confirm')->passwordInput() ?>

        <?php
        $roles = Yii::$app->authManager->getRoles();
        $roleList = [];
        foreach ($roles as $role) {
            $roleList[$role->name] = $role->description;
        }
        ?>
        <?= $form->field($model, 'role')->radioList($roleList) ?>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <?= Html::submitButton('Create Manager', ['class' => 'btn btn-primary', 'name' => 'create-manager']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
