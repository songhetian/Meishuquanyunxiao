<?php

namespace teacher\modules\v2\models;

use Yii;
use yii\helpers\Html;
use common\models\Campus;
use components\Oss;
use components\Spark;
use backend\models\ActiveRecord;
use yii\helpers\ArrayHelper;
use common\models\Format;
use common\models\Category;

class Video extends ActiveRecord
{
    const SOURCE_LOCAL = 10;
    const SOURCE_METIS = 20;
    const CHARGING_NORMAL = 10;
    const CHARGING_ENCRYPT = 20;
    const PUBLIC_NOT_YET = 0;
    const PUBLIC_ED = 10;

    public static function tableName()
    {
        return '{{%video}}';
    }

    public function fields()
    {
        $fields = parent::fields();
        
        $fields['video_id'] = function() {
            return $this->id;
        };
        $fields['preview_image'] = function () {
            $size = Yii::$app->params['oss']['Size']['750x500'];
            if($this->source == self::SOURCE_LOCAL){
                $studio = Campus::findOne(Admin::findOne($this->admin_id)->campus_id)->studio_id;
                return Oss::getUrl($studio, 'video', 'preview', $this->preview).$size;
            }else{
                 return $this->preview.$size;
            }
        };
        $fields['preview'] = function () {
            $size = Yii::$app->params['oss']['Size']['750x500'];
            if($this->source == self::SOURCE_LOCAL){
                $studio = Campus::findOne(Admin::findOne($this->admin_id)->campus_id)->studio_id;
                return Oss::getUrl($studio, 'video', 'preview', $this->preview).$size;
            }else{
                 return $this->preview.$size;
            }
        };

        $fields['cc_id'] = function(){
            return $this->cc_id;
        };

        $fields['duration'] = function(){
            return Spark::getDuration($this->cc_id, $this->charging_option);
        };
        
        $fields['studio_id'] = function () {
            return ($this->studio_id) ? $this->studio_id : Yii::t('api', 'Local Studio');
        };

        $fields['watch_count'] = function () {
            return $this->watch_count + 1;
        };
        $fields['title'] = function () {
            return $this->name;
        };
        $fields['charging_option'] = function() {
            return $this->charging_option/10;
        };
        $fields['description'] = function () {
            $host_info = Yii::$app->request->hostInfo.'/assets';
            $description = preg_replace('/api.teacher.meishuquanyunxiao.com/', 'backend.meishuquanyunxiao.com', str_replace('/assets', $host_info, $this->description));
            return preg_replace('/(http|https):\/\//', 'http://', $description);
        };
        if(!Spark::getDuration($this->cc_id, $this->charging_option))
        {   
            if($this->charging_option == self::CHARGING_NORMAL){
               $this->charging_option = self::CHARGING_ENCRYPT;
            }elseif($this->charging_option == self::CHARGING_ENCRYPT){
               $this->charging_option = self::CHARGING_NORMAL;
            }

        }

        unset(
            $fields['id'],
            $fields['source'],
            $fields['metis_material_id'],
            $fields['category_id'],
            $fields['keyword_id'],
            $fields['admin_id'],
            $fields['is_public'],
            $fields['created_at'], 
            $fields['updated_at'], 
            $fields['status'],
            $fields['name']
        );
        return $fields;
    }

    public function beforeSave($insert)
    {
        //公共处理
        $admin_id = Yii::$app->user->identity->id;
        $studio = Admin::findOne($admin_id)->studio_id;
        $this->preview = Oss::upload($this, $studio, 'video', 'preview');
        $this->keyword_id = (is_array($this->keyword_id)) ? Format::implodeValue($this->keyword_id) : NULL;
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
              
            }else{
                if ($this->preview && $this->preview != $this->getOldAttribute('preview')) {
                    Oss::delFile($studio, 'video', 'preview', $this->getOldAttribute('preview'));
                }else{
                    $this->preview = $this->getOldAttribute('preview');
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
        return true; 
    }

    public function rules()
    {
        return [
            //特殊需求
            [['name', 'category_id', 'cc_id'], 'required'],
            //字段规范
            ['source', 'default', 'value' => self::SOURCE_LOCAL], 
            ['source', 'in', 'range' => [self::SOURCE_LOCAL, self::SOURCE_METIS]],
            ['is_public', 'default', 'value' => self::PUBLIC_ED], 
            ['is_public', 'in', 'range' => [self::PUBLIC_NOT_YET, self::PUBLIC_ED]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['source', 'charging_option', 'metis_material_id', 'studio_id', 'instructor', 'category_id', 'watch_count', 'admin_id', 'is_public', 'created_at', 'updated_at', 'status'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['cc_id'], 'string', 'max' => 100],
            [['keyword_id'], 'safe'],
            ['preview', 'image', 
                'extensions' => 'jpg,jpeg,png',
                'maxSize' => 1024 * 5000,
                'minWidth' => 150,
                'minHeight' => 150,
            ],
            
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'source' => Yii::t('app', '来源'),
            'name' => Yii::t('app', '名称'),
            'charging_option' => Yii::t('app', '收费类型'),
            'metis_material_id' => Yii::t('app', '美术圈素材ID'),
            'studio_id' => Yii::t('app', '所属机构'),
            'instructor' => Yii::t('app', '所属讲师'),
            'category_id' => Yii::t('app', '分类'),
            'keyword_id' => Yii::t('app', '关键字'),
            'preview' => Yii::t('app', '预览图'),
            'cc_id' => Yii::t('app', '视频'),
            'watch_count' => Yii::t('app', '查看次数'),
            'description' => Yii::t('app', '详情'),
            'admin_id' => Yii::t('app', '上传者'),
            'is_public' => Yii::t('app', '是否公开'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public static function getValues($field, $value = false)
    {
        $values = [
            'source' => [
                self::SOURCE_LOCAL => Yii::t('common', 'Local'),
                self::SOURCE_METIS => Yii::t('common', 'Metis'),
            ],
            'is_public' => [
                self::PUBLIC_NOT_YET => Yii::t('common', 'Not Publiced'),
                self::PUBLIC_ED => Yii::t('common', 'Publiced'),
            ],
            'status' => [
                self::STATUS_DELETED => Yii::t('backend', 'Has Deleted'),
                self::STATUS_ACTIVE => Yii::t('backend',  'Not Deleted'),                
            ],
        ];

        return $value !== false ? ArrayHelper::getValue($values[$field], $value) : $values[$field];
    }

    public static function AddValue($model,$data,$admin_id)
    {
        $model->source = Video::SOURCE_METIS;
        $model->charging_option = $data->charging_option * 10;
        $model->name = ($data->title) ? $data->title : Yii::t('backend', 'Name Is Empty');
        $model->metis_material_id = $data->id;
        $model->studio_id = $data->studio_id;
        $category = current(explode(',', $data->category));
        $model->category_id = $category;
        $model->keyword_id = $data->keyword;
        $model->preview = $data->preview_image;
        $model->cc_id = $data->chapter;
        $model->admin_id = $admin_id;

        return $model;
    }

    public function getCategorys()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id'])->alias('categorys');
    }

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
    }
}
