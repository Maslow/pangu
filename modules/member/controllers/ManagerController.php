<?php

namespace app\modules\member\controllers;

use app\modules\member\models\CreateUserForm;
use app\modules\member\models\UpdateUserForm;
use Yii;
use app\modules\member\models\User;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ManagerController implements the CRUD actions for User model.
 */
class ManagerController extends Controller
{
    public $layout = 'manager';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     * @permission member.users.list
     */
    public function actionIndex()
    {
        if (!$this->checkAccess('member.users.list')) {
            return $this->goNotAllowed();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @permission member.users.view
     */
    public function actionView($id)
    {
        if (!$this->checkAccess('member.users.view')) {
            return $this->goNotAllowed();
        }

        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @permission member.users.create
     */
    public function actionCreate()
    {
        if (!$this->checkAccess('member.users.create')) {
            return $this->goNotAllowed();
        }

        $model = new CreateUserForm();

        if ($model->load(Yii::$app->request->post()) && $model->create()) {
            $this->sendFlashMessage('创建用户成功！');
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @permission member.users.update
     */
    public function actionUpdate($id)
    {
        if (!$this->checkAccess('member.users.update')) {
            return $this->goNotAllowed();
        }
        $user = $this->findModel($id);
        $model = new UpdateUserForm();

        if ($model->load(Yii::$app->request->post()) && $model->update()) {
            $this->sendFlashMessage('用户更新成功！');
            return $this->redirect(['index']);
        } else {
            $model->username = $user->username;
            return $this->render('update', [
                'model' => $model,
                'user' => $user
            ]);
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @permission member.users.delete
     */
    public function actionDelete($id)
    {
        if (!$this->checkAccess('member.users.delete')) {
            return $this->goNotAllowed();
        }
        if($this->findModel($id)->delete()){
            $this->sendFlashMessage("删除用户成功！");
        }else{
            $this->sendFlashMessage("删除用户失败！");
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * 判断当前登录管理员是否满足指定权限
     * @param $permission string 指定权限名
     * @return boolean
     */
    protected function checkAccess($permission)
    {
        /* @var $manager \yii\web\User */
        $manager = \Yii::$app->manager;
        if (!$manager->can($permission)) {
            $this->sendFlashMessage("您没有进行该操作的权限({$permission})!");
            return false;
        }
        return true;
    }

    /**
     * 用户无权限访问请求时，跳转到指定页面
     * @return \yii\web\Response
     */
    protected function goNotAllowed()
    {
        return $this->redirect(Yii::$app->params['route.not.allowed']);
    }

    /**
     * 发送通知信息到下一个请求页面
     * @param $message string 要发送的信息
     */
    protected function sendFlashMessage($message){
        \Yii::$app->session->setFlash(\Yii::$app->params['flashMessageParam'], $message);
    }
}
