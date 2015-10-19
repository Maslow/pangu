<?php
/**
 * Created by PhpStorm.
 * User: wangfugen
 * Date: 15/10/16
 * Time: 下午8:39
 */

namespace app\modules\rbac\models;


use yii\base\Model;
use yii\rbac\Role;

class CreateRoleForm extends Model
{
    public $name;
    public $description;
    public $data;

    public function rules()
    {
        return [
            [['name', 'description'], 'required'],
            ['name', 'string', 'min' => 3, 'max' => 16],
            ['description', 'string', 'min' => 3, 'max' => 16],
            ['data', 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => "标识",
            'description' => "标题",
            'data' => "备注"
        ];
    }

    /**
     * 验证表单并执行角色创建操作
     * @return bool
     */
    public function create()
    {
        if ($this->validate()) {
            $role = new Role();
            // 判断角色名是否已存在
            if($this->getAuth()->getRole($this->name)){
                $this->addError('name',"角色名已存在！");
                return false;
            }
            $role->name = $this->name;
            $role->description = $this->description;
            $role->data = $this->data;
            $this->getAuth()->add($role);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 莸取authManager组件
     * @return \yii\rbac\ManagerInterface
     */
    protected function getAuth(){
        return \Yii::$app->authManager;
    }
}