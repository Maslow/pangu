<?php

namespace app\modules\backend\models;

use app\modules\backend\Module;
use Yii;
use app\base\Event;
use yii\base\InvalidParamException;
use yii\base\Model;

/**
 * Class CreateForm
 * @package app\modules\backend\models
 */
class CreateForm extends Model
{
    public $username;
    public $password;
    public $password_confirm;
    public $role;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'role', 'password', 'password_confirm'], 'required'],
            [['username', 'password'], 'string', 'min' => 3, 'max' => 32],
            ['role', 'string', 'max' => 32],
            [['username'], 'unique', 'targetClass' => Manager::className()],
            ['password_confirm', 'compare', 'compareAttribute' => 'password']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('backend', 'Username'),
            'password' => Yii::t('backend', 'Password'),
            'password_confirm' => Yii::t('backend', 'Confirm Password'),
            'role' => Yii::t('backend', 'Role'),
        ];
    }

    /**
     * 验证表单，并执行保存操作
     * @return bool
     */
    public function create()
    {
        $event = new Event(['model' => $this]);
        if ($this->validate()) {
            $manager = new Manager();
            try {
                $manager->username = $this->username;
                $manager->password_hash = Yii::$app->security->generatePasswordHash($this->password);
                $manager->updated_at = time();
                $manager->created_at = time();
                $manager->auth_key = Yii::$app->security->generateRandomString();
                $manager->created_ip = Yii::$app->request->getUserIP();
                $manager->created_by = Yii::$app->manager->identity->id;
                $manager->locked = 0;

                if ($manager->save()) {
                    $role = $this->getAuth()->getRole($this->role);
                    if (!$role) {
                        throw new \InvalidArgumentException('The role is not exist!');
                    }
                    $this->getAuth()->assign($role, $manager->id);
                    Event::trigger(Module::className(), Module::EVENT_CREATE_MANAGER_SUCCESS, $event);
                    return true;
                } else {
                    throw new InvalidParamException();
                }
            } catch (\Exception $e) {
                Yii::error($manager->getErrors());
                Yii::error($e->getMessage());
                $this->addError('username', Yii::t('backend', 'Throw an exception of saving data!'));
            }
        }
        Event::trigger(Module::className(), Module::EVENT_CREATE_MANAGER_FAIL, $event);
        return false;
    }

    /**
     * @return \yii\rbac\ManagerInterface
     */
    protected function getAuth()
    {
        return Yii::$app->authManager;
    }
}