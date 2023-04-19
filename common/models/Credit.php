<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%credit}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $content
 * @property integer $credit
 * @property integer $admin_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Credit extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%credit}}';
    }

    public function rules()
    {
        return [
            //特殊需求
            [['user_id', 'content', 'credit', 'admin_id'], 'required'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['user_id', 'credit', 'admin_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['content'], 'string', 'max' => 200],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', '用户ID'),
            'content' => Yii::t('app', '内容'),
            'credit' => Yii::t('app', '学分'),
            'admin_id' => Yii::t('app', '上传者'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }
}
