<?php

namespace app\modules\rbac\controllers;

use app\base\Event;
use app\base\CheckAccessTrait;
use app\modules\backend\models\Manager;
use app\modules\rbac\models\CreateRoleForm;
use app\modules\rbac\models\UpdateRoleForm;
use app\modules\rbac\Module;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class ManagerController extends Controller
{
    use CheckAccessTrait;

    public $layout = '/manager';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete-role' => ['post'],
                ],
            ],
        ];
    }

    /**
     * 显示角色列表
     * @return string
     * @permission rbac.roles.list
     */
    public function actionRoles()
    {
        if (!$this->checkAccess('rbac.roles.list')) {
            return $this->goNotAllowed();
        }

        return $this->render('roles', ['roles' => $this->getAuth()->getRoles()]);
    }

    /**
     * 新建一个角色
     * @return string|\yii\web\Response
     * @permission rbac.roles.create
     */
    public function actionCreateRole()
    {
        if (!$this->checkAccess('rbac.roles.create')) {
            return $this->goNotAllowed();
        }

        Event::trigger(Module::className(),Module::EVENT_BEFORE_CREATE_ROLE);

        $model = new CreateRoleForm();
        if ($model->load(\Yii::$app->request->post()) && $model->create()) {
            return $this->redirect(['roles']);
        }
        return $this->render('create-role', ['model' => $model]);
    }

    /**
     * 更新指定角色
     * @param $name string 指定角色的name
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @permission rbac.roles.update
     */
    public function actionUpdateRole($name)
    {
        if (!$this->checkAccess('rbac.roles.update')) {
            return $this->goNotAllowed();
        }

        Event::trigger(Module::className(),Module::EVENT_BEFORE_UPDATE_ROLE);

        $role = $this->getAuth()->getRole($name);
        if (!$role) {
            throw new NotFoundHttpException(\Yii::t('rbac', 'The role named "{role}" is not exist!',['role'=>$name]));
        }

        $model = new UpdateRoleForm();

        if ($model->load(\Yii::$app->request->post()) && $model->update()) {
            return $this->redirect(['roles']);
        }

        if (\Yii::$app->request->isGet) {
            $model->name = $role->name;
            $model->description = $role->description;
            $model->data = $role->data;
        }

        // 生成权限列表数组，供checkboxList做为参数使用
        $permissionObjects = $this->getAuth()->getPermissions();
        $permissions = [];
        foreach ($permissionObjects as $permission) {
            $permissions[$permission->name] = $permission->description;
            // 如果是Get请求，生成$model->permissions的值
            if (\Yii::$app->request->isGet && $this->getAuth()->hasChild($role, $permission)) {
                $model->permissions[] = $permission->name;
            }
        }
        return $this->render('update-role', ['model' => $model, 'permissions' => $permissions]);
    }

    /**
     * 删除指定角色
     * @param $name string 指定角色标识
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @permission rbac.roles.delete
     */
    public function actionDeleteRole($name)
    {
        if (!$this->checkAccess('rbac.roles.delete')) {
            return $this->goNotAllowed();
        }
        Event::trigger(Module::className(), Module::EVENT_BEFORE_DELETE_ROLE);
        $role = $this->getAuth()->getRole($name);
        if (!$role) {
            throw new NotFoundHttpException(\Yii::t('rbac', 'The role is not exist!'));
        }

        // 是否存在为该角色的管理员
        /* @var $managers \app\modules\backend\models\Manager[] */
        $managers = Manager::find()->all();
        foreach($managers as $man){
            if($this->getAuth()->getAssignment($name,$man->id)){
                Event::trigger(Module::className(),
                    Module::EVENT_DELETE_ROLE_FAIL,
                    new Event(['role'=>$role ,'error'=>\Yii::t('rbac','This role can not be deleted unless the role of any user is it.')])
                );
                return $this->redirect(['roles']);
            }
        }

        if ($this->getAuth()->remove($role)) {
            Event::trigger(Module::className(), Module::EVENT_DELETE_ROLE_SUCCESS,new Event(['role'=>$role]));
        } else {
            Event::trigger(Module::className(), Module::EVENT_DELETE_ROLE_FAIL,new Event(['role'=>$role]));
        }
        return $this->redirect(['roles']);
    }

    /**
     * 返回应用权限管理组件对象
     * @return \yii\rbac\ManagerInterface
     */
    protected function getAuth()
    {
        return \Yii::$app->authManager;
    }
}