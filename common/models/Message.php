<?php

namespace common\models;

use Yii;
use backend\models\ActiveRecord;
use components\Jpush;
use common\models\MessageCategory;
use common\models\User;
use backend\models\Admin;

/**
 * This is the model class for table "{{%message}}".
 *
 * @property integer $id
 * @property integer $message_category_id
 * @property string $campus_id
 * @property string $category_id
 * @property string $class_id
 * @property integer $user_id
 * @property string $title
 * @property string $content
 * @property integer $correlated_id
 * @property integer $code
 * @property integer $admin_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Message extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%message}}';
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->campus_id = (is_array($this->campus_id)) ? Format::implodeValue($this->campus_id) : NULL;
            $this->category_id = (is_array($this->category_id)) ? Format::implodeValue($this->category_id) : NULL;
            $this->class_id = (is_array($this->class_id)) ? Format::implodeValue($this->class_id) : NULL;
            if ($this->isNewRecord) {
                $this->admin_id = Yii::$app->user->identity->id;
                $category = MessageCategory::findOne(['name' => Yii::t('common', 'My Message')]);
                if($category && $this->message_category_id != $category->id){
                    $this->code = 9000;
                }
            }
            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        \backend\models\AdminLog::saveLog($this);
        //群发消息
        $category = MessageCategory::findOne(['name' => Yii::t('common', 'My Msg')]);
        if($category && $this->message_category_id != $category->id){
            $client = new Jpush();
            $client->sendAllNotification($this->title, ['code' => 9000]);
        }
        return true; 
    }

    public function rules()
    {
        return [
            //特殊需求
            [['message_category_id', 'campus_id', 'title'], 'required'],
            //字段规范
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['message_category_id', 'user_id', 'correlated_id', 'code', 'admin_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 32],
            [['campus_id', 'category_id', 'class_id'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'message_category_id' => Yii::t('app', '消息类型'),
            'campus_id' => Yii::t('app', '校区'),
            'category_id' => Yii::t('app', '科目'),
            'class_id' => Yii::t('app', '班级'),
            'user_id' => Yii::t('app', '用户'),
            'title' => Yii::t('app', '标题'),
            'content' => Yii::t('app', '内容'),
            'correlated_id' => Yii::t('app', '关联ID'),
            'code' => Yii::t('app', '编码'),
            'admin_id' => Yii::t('app', '发布者'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public function getMessageCategorys()
    {
        return $this->hasOne(MessageCategory::className(), ['id' => 'message_category_id'])->alias('message_categorys');
    }

    public function getUsers()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])->alias('users');
    }

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
    }
}