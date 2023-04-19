<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;
use backend\models\Menu;
use common\models\Format;

/**
 * This is the model class for table "{{%auth_item}}".
 *
 * @property string $name
 * @property integer $pid
 * @property integer $studio_id
 * @property integer $type
 * @property string $scope
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Rbac extends ActiveRecord
{
    const TYPE_ROLE = 1;
    const TYPE_PERMISSION = 2;
    const PERMISSION_BACKEND = 10;
    const PERMISSION_DEV = 20;
    const PID_IS_NULL = 0;
    public $permission;

    public static function tableName()
    {
        return '{{%auth_item}}';
    }

    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            $studio_id = Format::getStudio('id');
            $max = self::find()->where([
                'studio_id' => $studio_id,
                'type' => self::TYPE_ROLE
            ])->max('name');

            $main = (17 * 10000 + $studio_id) * 1000 + 1;

            $number = ($max) ? $main + ltrim(substr($max, -3, 3), 0) : $main;
            $this->name = (string)$number;
            $this->studio_id = $studio_id;
            $this->type = self::TYPE_ROLE;
        }else{
            $auth = Yii::$app->authManager;
            $role = $auth->getRole($this->name);
            $auth->removeChildren($role);
            if(is_array($this->permission)){
                $this->addPermission($auth, $role, $this->permission);
            }
        }
        return parent::beforeValidate();
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        //创建角色对应权限
        if($insert) {
            $auth = Yii::$app->authManager;
            $role = $auth->getRole($this->name);
            if(is_array($this->permission)){
                $this->addPermission($auth, $role, $this->permission);
            }
        }
        
        \backend\models\AdminLog::saveLog($this);
        return true; 
    }

    public function rules()
    {
        return [
            //特殊需求
            [['name', 'description'], 'required'],
            [['name'], 'unique'],
            [['permission'], 'required', 'on' => 'role'],
            //字段规范
            ['pid', 'default', 'value' => self::PID_IS_NULL], 
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['pid', 'studio_id', 'type', 'created_at', 'updated_at', 'status'], 'integer'],
            [['data'], 'string'],
            [['name', 'scope', 'description', 'rule_name'], 'string', 'max' => 64]
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', '名称'),
            'pid' => Yii::t('app', '上级'),
            'studio_id' => Yii::t('app', '所属画室'),
            'type' => Yii::t('app', '类型'),
            'scope' => Yii::t('app', '生效范围'),
            'permission' => Yii::t('app', '权限'),
            'description' => Yii::t('app', '职位名称'),
            'rule_name' => Yii::t('app', '规则名称'),
            'data' => Yii::t('app', '数据'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public static function getRoles()
    {
        $studio_id = Format::getStudio('id');
        $model = static::find()
            ->where([
                'studio_id' => $studio_id,
                'type' => self::TYPE_ROLE, 
                'status' => self::STATUS_ACTIVE
            ])
            ->orderBy('created_at')
            ->all();
        return ArrayHelper::map($model, 'name', 'description');
    }

    public function getRolePermission($auth, $name)
    {
        $permissions = $auth->getChildren($name);
        foreach ($permissions as $permission) {
            $res[] = $permission->name;
        }
        return $res;
    }

    public function getPermissions()
    {
        foreach (Menu::menuList() as $value) {
            foreach (Menu::menuList($value->id) as $v) {
               if(Yii::$app->user->identity->studio_id != 183 && in_array($value->id, Yii::$app->params['artId'])) {
                    continue;
               }
               if(Yii::$app->user->identity->studio_id != 322 && in_array($value->id, array(47))) {
                    continue;
               }
                $model = static::find()
                    ->andFilterWhere(['like', Format::concatField('scope'), Format::concatString(self::PERMISSION_BACKEND)])
                    ->andFilterWhere(['name' => $v->route, 'type' => self::TYPE_PERMISSION, 'status' => self::STATUS_ACTIVE])
                    ->one();
                if($model){
                    $res[$value->name][$model->name] = $model->description;
                    $permissions = static::find()
                        ->where(['type' => self::TYPE_PERMISSION, 'status' => self::STATUS_ACTIVE])
                        ->andFilterWhere(['like', Format::concatField('scope'), Format::concatString(self::PERMISSION_BACKEND)])
                        ->andFilterWhere(['like', 'name', $v->route . '/'])
                        ->all();
                    foreach ($permissions as $permission) {
                        $res[$value->name][$permission->name] = '—　' . $permission->description;
                    }
                }
                
            }
        }
        return $res;
    }

    public static function getTemplate($action, $extends = [])
    {
        $template = '';
        //额外处理
        if($extends){
            foreach ($extends as $name) {
                if(Yii::$app->user->can($action . '/'. $name)){
                    $template .= '{'. $name .'} ';
                }
            }
        }

        $filters = ['view', 'update', 'delete'];
        foreach ($filters as $route) {
            if(Yii::$app->user->can($action . '/'. $route)){
                $template .= '{'. $route .'} ';
            }
        }
        return $template;
    }

    public static function getParents($pid){
        if(!(strpos(Yii::t('backend', 'No Parent'), $pid) === false)){
            $ids[] = 0;
        }
        $roles = Rbac::find()
            ->andFilterWhere(['type' => self::TYPE_ROLE])
            ->andFilterWhere(['like', 'description', $pid])
            ->all();
        if($roles){
            foreach ($roles as $value) {
                $ids[] = $value->name;
            }
        }
        return $ids;
    }

    public function addPermission($auth, $role, $permission)
    {
        foreach ($permission as $name) {
            $permission = $auth->createPermission($name);
            $auth->addChild($role, $permission);
        }
    }
}