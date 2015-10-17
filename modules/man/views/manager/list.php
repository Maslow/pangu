<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '管理员列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-default">

    <div class="panel-heading"><?= Html::encode($this->title) ?></div>
    <div class="panel-body">
        <?= Html::a('创建管理员', ['create'], ['class' => 'btn btn-default']) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'username',
            [
                'value' => function ($row) {
                    $roles = Yii::$app->authManager->getRolesByUser($row->id);
                    if ($role = current($roles)) {
                        return $role->description;
                    } else {
                        return '-';
                    }
                },
                'label' => '角色'
            ],
            [
                'attribute' => 'created_by',
                'value' => function ($row) {
                    $c = \app\modules\man\models\Manager::findOne($row->created_by);
                    return $c->username;
                },
            ],
            'created_ip',
            'created_at:datetime',
            'updated_at:datetime',
            [
                'class' => '\yii\grid\ActionColumn',
                'template' => '{update} {delete}',
            ],
        ],
    ]); ?>

</div>