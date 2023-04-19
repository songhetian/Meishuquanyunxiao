<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use backend\models\ActiveRecord;
use backend\models\Admin;
use common\models\Picture;
use common\models\Video;

/**
 * This is the model class for table "{{%souce_group}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $admin_id
 * @property string $souce_id
 * @property integer $type
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class SouceGroup extends ActiveRecord
{
    const TYPE_PICTURE = 10;
    const TYPE_VIDEO = 20;
    const IS_MAIN  = 10;
    const NOT_MAIN = 0;
    const IS_PUBLIC = 10;
    const NOT_PUBLIC = 0;
    const ROLE_TEACHER = 10;
    const ROLE_STUDENT = 20;

    public static function tableName()
    {
        return '{{%souce_group}}';
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->admin_id = Yii::$app->user->identity->id;
                $this->role = self::ROLE_TEACHER;
                $this->is_main = self::NOT_MAIN;
                $this->is_public = self::NOT_PUBLIC;
            }else{

            }
            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        \backend\models\AdminLog::saveLog($this);
        return true; 
    }

    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['admin_id', 'type', 'created_at', 'updated_at', 'is_public','status'], 'integer'],
            [['material_library_id'], 'string'],
            [['name'], 'string', 'max' => 32],
            ['type', 'in', 'range' => [self::TYPE_PICTURE, self::TYPE_VIDEO]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            ['is_public', 'in', 'range' => [self::IS_PUBLIC, self::NOT_PUBLIC]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', '名称'),
            'admin_id' => Yii::t('app', '上传者'),
            'material_library_id' => Yii::t('app', '素材'),
            'is_main' => Yii::t('app', '主分组'),
            'is_public' => Yii::t('app', '是否公开'),
            'type' => Yii::t('app', '类型'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public static function getValues($field, $value = false)
    {
        $values = [
            'is_public' => [
                self::IS_PUBLIC => Yii::t('backend', 'Is Public'),
                self::NOT_PUBLIC => Yii::t('backend', 'Not Public'),
            ],
            'status' => [
                self::STATUS_DELETED => Yii::t('backend', 'Has Deleted'),
                self::STATUS_ACTIVE => Yii::t('backend',  'Not Deleted'),                
            ],
        ];

        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }

    public function isPublic()
    {
        $this->is_public = ($this->is_public) ? self::NOT_PUBLIC : self::IS_PUBLIC;
        if($this->save()){
            return true;
        }
        return false;
    }

    public static function getGroupList($type, $gid = NULL)
    {
        $res = [];
        $model = static::find()
            ->andFilterWhere(['type' => $type, 'admin_id' => Yii::$app->user->identity->id, 'status' => self::STATUS_ACTIVE])
            ->andFilterWhere(['id' => $gid])
            ->all();

        if($model){
            foreach ($model as $v) {
                $res[$v->id] = $v->name;
            }
        }
        return $res;
    }

    //判断分组是否存在
    public static function Exists($admin_id,$type,$role) {
        $names = self::find()->select('name')
                               ->where(['admin_id'=>$admin_id,'type'=>$type,'status'=>self::STATUS_ACTIVE,'role'=>$role])
                               ->asArray()
                               ->all();

        return array_column($names, 'name');
    }

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
    }
}
