<?php

namespace common\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use backend\models\Admin;
use backend\models\ActiveRecord;
use common\models\Campus;
use common\models\Group;
use common\models\Query;
use common\models\Format;
use common\models\Curl;
use components\Oss;

/**
 * This is the model class for table "{{%course_material}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $admin_id
 * @property integer $is_public
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class CourseMaterial extends ActiveRecord
{
    const PUBLIC_NOT_YET = 0;
    const PUBLIC_ED = 10;

    public $picture;
    public $video;

    public static function tableName()
    {
        return '{{%course_material}}';
    }

    public function beforeSave($insert)
    {
        //公共处理
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->admin_id = Yii::$app->user->identity->id;
            }else{
            }
            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if($this->picture){
            $this->explodeGroup($this->picture, Group::TYPE_PICTURE); 
        }
        if($this->video){
            $this->explodeGroup($this->video, Group::TYPE_VIDEO);
        }
        \backend\models\AdminLog::saveLog($this);
        return true; 
    }

    public function rules()
    {
        return [
            //特殊需求
            [['name'], 'required'],
            //字段规范
            ['is_public', 'default', 'value' => self::PUBLIC_ED], 
            ['is_public', 'in', 'range' => [self::PUBLIC_NOT_YET, self::PUBLIC_ED]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['description'], 'string'],
            [['admin_id', 'is_public', 'created_at', 'updated_at', 'status'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['picture', 'video'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', '课题名称'),
            'description' => Yii::t('app', '图文'),
            'admin_id' => Yii::t('app', '上传者'),
            'is_public' => Yii::t('app', '是否公开'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public function explodeGroup($value, $type)
    {
        $arrs = explode('/', substr($value, 0, -1));
        for ($i = 0; $i < count($arrs); $i++) { 
            $arr = explode('-', $arrs[$i]);
            $res[$arr[0]] .= ($res[$arr[0]]) ? ','. $arr[1] : $arr[1];            
        }
        //禁用被删除的分组
        $actives = Group::findAll(['course_material_id' => $this->id, 'type' => $type, 'status' => Group::STATUS_ACTIVE]);
        foreach ($actives as $active) {
            if(!in_array($active->name, array_keys($res))){
                $active->status = Group::STATUS_DELETED;
                $active->save();
            }
        }
        if($res = $this->contrast($res, $type)){
            if($res){
                foreach ($res as $name => $material_library_id) {
                    $model = new Group();
                    $model->course_material_id = $this->id;
                    $model->type = $type;
                    $model->name = (string)$name;
                    $model->material_library_id = $material_library_id;
                    $model->save();
                }
            }
        }
        return true;
    }
    
    public function contrast($res, $type)
    {
        $isset = Group::getGroupList($this->id, $type);
        if($isset){
            $arr = [];
            foreach ($res as $key => $value) {
                $model = Group::findOne(['course_material_id' => $this->id, 'name' => $key, 'type' => $type,'status' => Group::STATUS_ACTIVE]);
                if($model){
                    //发生了变更
                    if($model->material_library_id != $value){
                        $model->material_library_id = $value;
                        $model->save();
                    }
                }else{
                    //添加了新数据
                    $arr[$key] = $value;
                }
            }
            return $arr;
        }
        return $res;
    }

    public static function getCourseMaterialList()
    {
        $query = static::find();
        $ids = Query::visible(self::className(), ['admins.campus_id', 'admins.category_id', 'admins.class_id'], 'or like');
        if(is_array($ids)){
            $query->andFilterWhere([self::tableName() . '.id' => $ids]);
        }
        $model = $query
            ->andFilterWhere(['status' => self::STATUS_ACTIVE])
            ->all();
        return ArrayHelper::map($model, 'id', 'name');
    }

    //获取本地素材库数据
    public static function getMaterials($table, $page = 0)
    {
        $limit = 30;
        $offset = $page * $limit;
        //可见范围筛选
        $query = $table::find();

        $ids = Query::visible($table::className(), ['admins.campus_id', 'admins.category_id', 'admins.class_id'], 'or like');
        if(is_array($ids)){
            $query->andFilterWhere([$table::tableName() . '.id' => $ids]);
        }

        $query->andFilterWhere([
            'source' => $table::SOURCE_LOCAL, 
            'status' => $table::STATUS_ACTIVE
        ]);
        
        $number = clone $query;
        $model = $query->orderBy('created_at DESC, id')
            ->limit($limit)
            ->offset($offset)
            ->all();
        return [
            'material' => $model,
            'count' => $number->count()
        ];
    }

    //获取美术圈数据
    public static function getMetisMaterials($type, $category_id = 0, $category_child_id = 0, $keyword_id = 0, $publishing_id = 0, $page = 0 ,$pic_limit = 30)
    {
        //云校的类型转换美术圈的类型
        if($type == Group::TYPE_PICTURE){
            $type = 3;
            $ids = '&ids=207,208';
        }else{
            $type = 2;
            $ids = '&ids=194,195,5';
        }
        $category = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['category'].'?parent_id=0&type='.$type.$ids
        );

        $category_id = ($category_id) ? $category_id : $category[0]->id;
        $category_child = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['category'].'?parent_id='.$category_id.'&type='.$type
        );

        $category_child_id = ($category_child_id) ? $category_child_id: $category_child[0]->id;
        $keyword = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['keyword'].'?category_id='.$category_child_id.'&type='.$type
        );

        $publishing = Curl::metis_file_get_contents(
            Yii::$app->params['metis']['Url']['publishing']
        );

        if($type == 3){
            $material = Curl::metis_file_get_contents(
               Yii::$app->params['metis']['Url']['commoditys'].'?category='.$category_child_id.'&keyword='.$keyword_id.'&publishing_company='.$publishing_id.'&limit='.$pic_limit.'&page='.$page
            );
            $count = Curl::metis_file_get_contents(
               Yii::$app->params['metis']['Url']['commodity_num'].'?category='.$category_child_id.'&keyword='.$keyword_id.'&publishing_company='.$publishing_id
            );
        }else{
            $courses = Curl::metis_file_get_contents(
                Yii::$app->params['metis']['Url']['course'].'?category='.$category_child_id.'&keyword='.$keyword_id.'&publishing_company='.$publishing_id.'&limit=5&page='.$page
            );
            $count = Curl::metis_file_get_contents(
               Yii::$app->params['metis']['Url']['course_num'].'?category='.$category_child_id.'&keyword='.$keyword_id.'&publishing_company='.$publishing_id
            );
            $material = [];
            foreach ($courses as $value) {
                $chapter = [];
                $chapters = Curl::metis_file_get_contents(
                    Yii::$app->params['metis']['Url']['course-chapters'].'?course_id='.$value->id
                );
                foreach ($chapters as $v) {
                    $chapter[] = (object)[
                        'id' => $v->id,
                        'title' => $v->title,
                        'charging_option' => $v->charging_option,
                        'chapter' => $v->chapter,
                        'preview_image' => $v->preview_image
                    ];
                }
                $material[] = (object)[
                    'id' => $value->id,
                    'title' => $value->title,
                    'chapter' => (object)$chapter
                ];
            }
        }
        return [
            'count' => current($count)->num,
            'category' => $category,
            'category_child' => $category_child,
            'keyword' => $keyword,
            'publishing' => $publishing,
            'material' => $material
        ];
    }

    //美术圈素材搜索功能
    public static function metisSearch($type, $search, $page = 0 ,$limit = 30)
    {
        if($type == Group::TYPE_PICTURE){
            $material = Curl::metis_file_get_contents(
                Yii::$app->params['metis']['Url']['picture_search'].'?search_input='.$search.'&limit='.$limit.'&page='.$page
            );
            $count = Curl::metis_file_get_contents(
               Yii::$app->params['metis']['Url']['picture_search_num'].'?search_input='.$search
            );
        }else{
            $courses = Curl::metis_file_get_contents(
                Yii::$app->params['metis']['Url']['course_search'].'?search_input='.$search.'&limit=5&page='.$page
            );
            $count = Curl::metis_file_get_contents(
               Yii::$app->params['metis']['Url']['course_search_num'].'?search_input='.$search
            );
            $material = [];
            foreach ($courses as $value) {
                $chapter = [];
                $chapters = Curl::metis_file_get_contents(
                    Yii::$app->params['metis']['Url']['course-chapters'].'?course_id='.$value->id
                );
                foreach ($chapters as $v) {
                    $chapter[] = (object)[
                        'id' => $v->id,
                        'title' => $v->title,
                        'charging_option' => $v->charging_option,
                        'chapter' => $v->chapter,
                        'preview_image' => $v->preview_image
                    ];
                }
                $material[] = (object)[
                    'id' => $value->id,
                    'title' => $value->title,
                    'chapter' => (object)$chapter
                ];
            }
        }
        
        return [
            'count' => current($count)->num,
            'material' => $material
        ];
    }
    
    static function concatLocalFilters($res, $table, $tag, $field, $page = 0)
    {
        $html .= "<div class='" . $tag . "-page'>";
        $html .= "<div class='" . $tag . "-page-" . $page . "'>";
        $html .= self::concatLocalMaterial($res, $table, $tag, $field);
        $html .= "</div>";
        $html .= "</div>";
        return $html;

    }

    //拼接过滤器
    static function concatFilters($type, $res, $page = 0)
    {
        $tag = ($type == Group::TYPE_PICTURE) ? 'picture' : 'video';
        $html .= "<section class='tile transparent bg-transparent-black-3'>";
            $html .= "<div class='".$tag."-filter-view'>";
                $html .= "<div class='tile-widget color transparent-black rounded-top-corners'>";
                    $html .= "<div class='input-group search-bar' type='".$type."' style='width:30%;float:right;'>";
                        $html .= "<input type='text' class='form-control' name='search' placeholder='请输入想要搜索的内容...'>";
                        $html .= "<span class='input-group-btn'>";
                            $html .= "<button class='btn btn-default' type='button'><i class='fa fa-search'></i> 搜索</button>";
                        $html .= "</span>";
                    $html .= "</div>";

                    $html .= "<ul class='tile-navbar' type='".$type."'>";
                        $html .= '<h5><strong>科目</strong></h5>';
                        $html .= "<li class='filters select category' line='1' url='".Yii::$app->urlManager->createUrl(['course-material/metis-category-filter'])."'>";
                            $html .= self::concatCategory($res['category']);
                        $html .= '</li>';

                        $html .= '<h5><strong>分类</strong></h5>';
                        $html .= "<li class='filters select category-child' line='2' url='".Yii::$app->urlManager->createUrl(['course-material/metis-category-child-filter'])."'>";
                            $html .= self::concatCategoryChild($res['category_child']);
                        $html .= '</li>';

                        $html .= '<h5><strong>关键字</strong></h5>';
                        $html .= "<li class='filters multi-select keyword' line='3' url='".Yii::$app->urlManager->createUrl(['course-material/metis-keyword-filter'])."'>";
                            $html .= self::concatKeyword($res['keyword']);
                        $html .= '</li>';
                        /*
                        if($type == Group::TYPE_PICTURE){
                            $html .= '<h5><strong>出版社</strong></h5>';
                            $html .= "<li class='filters select publishing' line='4' url='".Yii::$app->urlManager->createUrl(['course-material/metis-publishing-filter'])."'>";
                                $html .= self::concatPublishing($res['publishing']);
                            $html .= '</li>';
                        }
                        */
                    $html .= "</ul>";
                $html .= "</div>";

                $id = ($type == Group::TYPE_PICTURE) ? 'picture' : 'video';
                $html .= "<div class='filters-".$id."'>";
                    $html .= "<div class='category" . current($res['category'])->id. "-category-child" . current($res['category_child'])->id . "-page".$page."'>";
                        $html .= self::concatMetisMaterial($type, $res);
                    $html .= "</div>";
                $html .= "</div>";
            $html .= "</div>";

            $html .= "<div style='display:none;' class='".$tag."-search-view'>";
                $html .= "<div class='tile-widget color transparent-black rounded-top-corners'>";
                    $html .= "<ul class='tile-navbar'>";
                        $html .= "<h5 style='width:90%;float:left;'>结果: 找到相关数据 <strong class='".$tag."search_num'>81</strong> 个</h5>";
                        $html .= "<button type='button' class='btn btn-default back' tag=".$type."><i class='fa fa-reply'> 点击返回</i></button>";
                    $html .= "</ul>";
                $html .= "</div>";
                $html .= "<div class='search-".$id."'>";
                $html .= "</div>";
            $html .= "</div>";
        $html .= "</section>";
        return $html;
    }


    //拼接分类
    static public function concatCategory($res)
    {
        $num = 1;
        
        foreach ($res as $value) {
            if($num == 1){
                $html .= Html::a(
                  $value->name,
                  'javascript:void(0);',
                  ['class' => 'active', 'vid' => $value->id]
                );
            }else{
                $html .= Html::a(
                  $value->name,
                  'javascript:void(0);',
                  ['vid' => $value->id]
                );
            }
            $num ++;
        }
        
        return $html;
    }

    //拼接二级分类
    static public function concatCategoryChild($res)
    {
        $num = 1;
        
        foreach ($res as $value) {
            if($num == 1){
                $html .= Html::a(
                  $value->name,
                  'javascript:void(0);',
                  ['class' => 'active', 'vid' => $value->id]
                );
            }else{
                $html .= Html::a(
                  $value->name,
                  'javascript:void(0);',
                  ['vid' => $value->id]
                );
            }
            $num ++;
        }
        return $html;
    }

    //拼接关键字
    static public function concatKeyword($res)
    {
        foreach ($res as $key => $value) {
            //关键字根据priority字段换行
            if($key != 0){
                $prev = substr($res[$key - 1]->priority, 0, 1);
                $current = substr($res[$key]->priority, 0, 1);
                if($current != $prev){
                    $html .= '<br>';
                }
            }
            $html .= Html::a(
              $value->name,
              'javascript:void(0);',
              ['vid' => $value->id]
            );
        }
        return $html;
    }

    //拼接出版社
    static public function concatPublishing($res)
    {
        $html .= Html::a(
          '全部',
          'javascript:void(0);',
          ['class' => 'active']
        );

        foreach ($res as $value) {
            $html .= Html::a(
              $value->studio_name,
              'javascript:void(0);',
              ['vid' => $value->id]
            );
        }
        return $html;
    }

    static function concatLocalMaterial($res, $table, $tag, $field)
    {
        $pic_none = Yii::$app->request->baseUrl."/assets/images/pic-none.png";
        foreach ($res['material'] as $value) {
            $size = Yii::$app->params['oss']['Size']['250x250'];
            $studio = Campus::findOne(Admin::findOne($value->admin_id)->campus_id)->studio_id;
            $src = ($value->source == $table::SOURCE_LOCAL) ? Oss::getUrl($studio, $tag, $field, $value->$field) : $value->$field;
            $src = ($src) ? $src . $size : $pic_none;
            $html .= "<div class='img-view'>";
            if($tag == 'picture'){
                $html .= "<img alt='" . $value->id . "' src='" . $src . "'>";
            }else{
                $html .= "<img cc_id='" . $value->cc_id . "' alt='" . $value->id . "' src='" . $src . "'>";
            }
            $html .= "<div class='overlay'>";
            $html .= "<div class='media-info'>";
            $html .= "<div class='checkbox' style='margin-top: -3px !important'>";
            $html .= "<input type='checkbox' id='" . $tag . "-selectimg" . $value->id . "'>";
            $html .= "<label for='" . $tag . "-selectimg" . $value->id . "'></label>";
            $html .= "</div>";
            $name = ($value->name) ? Format::mb_substr($value->name, false, 0, 5) : Yii::t('backend', 'Name Is Empty') ;
            $html .= "<h2>" . $name . "</h2>";
            $html .= "</div>";
            $html .= "</div>";
            if($tag == 'picture'){
                $html .= "<div class='img-button'>";
                $html .= "<button type='button' src='" . $src . "' class='btn btn-primary btn-sm margin-bottom-20 larger'>查看</button> ";
                $html .= "<a href='" . $src . "' download='" . time() . "' class='btn btn-primary btn-sm margin-bottom-20 down'>下载</a>";
                $html .= "</div>";
            }else{
                $html .= "<div class='img-button'>";
                $html .= "<a href='#video-play' cc_id=" . $value->cc_id . " data-toggle='modal' class='btn btn-primary btn-sm margin-bottom-20 play'>播放</a> ";
                $html .= "</div>";
            }
            $html .= "</div>";
        }
        return ($html) ? $html : Yii::t('backend', 'Data Is Empty');
    }

    static function concatMetisMaterial($type, $res, $keyword_id = 0, $publishing_id = 0, $search = '')
    {
        $id = ($type == Group::TYPE_PICTURE) ? 'picture' : 'video';
        $pic_none = Yii::$app->request->baseUrl."/assets/images/pic-none.png";
        $size = Yii::$app->params['oss']['Size']['250x250'];
        if($keyword_id){ $additional .= '-' . $keyword_id; }
        if($publishing_id){ $additional .= '-' . $publishing_id; }
        if($search){ $additional .= '-' . $search; }
        foreach ($res['material'] as $value) {
            if($type == Group::TYPE_PICTURE){
                $html .= "<div class='img-view'>";
                $src = ($value->image) ? $value->image . $size : $pic_none;
                $html .= "<img class='img-thumbnail' alt='" . $value->id . "' src=" . $src . ">";
                $html .= "<div class='overlay'>";
                $html .= "<div class='media-info'>";
                $html .= "<div class='checkbox' style='margin-top: -3px !important'>";
                $html .= "<input type='checkbox' id='metis-".$id."-selectimg" . $value->id . $additional . "'>";
                $html .= "<label for='metis-".$id."-selectimg" . $value->id . $additional . "'></label>";
                $html .= "</div>";
                $name = ($value->name) ? Format::mb_substr($value->name, false, 0, 5) : Yii::t('backend', 'Name Is Empty');
                $html .= "<h2>" . $name . "</h2>";
                $html .= "</div>";
                $html .= "</div>";
                $html .= "<div class='img-button'>";
                $html .= "<button type='button' src='" . $src . "' class='btn btn-primary btn-sm margin-bottom-20 larger'>查看</button> ";
                $html .= "<a href='" . $src . "' download='" . time() . "' class='btn btn-primary btn-sm margin-bottom-20 down'>下载</a>";
                $html .= "</div>";
                $html .= "</div>";
            }else{
                $cover = current($value->chapter)->preview_image;
                $cover = ($cover) ? $cover . Yii::$app->params['oss']['Size']['375x250'] : $pic_none;
                $html .= "<div class='panel-group accordion'>";
                $html .= "<div class='panel panel-default'>";
                $html .= "<div class='panel-heading'>";
                $html .= "<h4 class='panel-title'>";
                $html .= "<a data-toggle='collapse' data-parent='#accordion' href='#course". $value->id . str_replace(',', '-', $additional) . "' class='collapsed'>";
                $html .= "<img width='15%' src='" . $cover ."'>";
                $html .= "<strong class='cover-title'>". $value->title ."</strong>";
                $html .= "<strong class='cover-count'>已更新至第". count((array)$value->chapter) ."节</strong>";
                $html .= "</a>";
                $html .= "</h4>";
                $html .= "</div>";
                $html .= "<div id='course". $value->id . str_replace(',', '-', $additional) . "' class='panel-collapse collapse' style='height: 0px;'>";
                    foreach ($value->chapter as $v) {
                        $html .= "<div class='img-view'>";
                        $src = ($v->preview_image) ? $v->preview_image . $size : $pic_none;
                        $html .= "<img class='img-thumbnail' cc_id='" . $v->cc_id . "' alt='" . $v->id . "' src=" . $src . ">";
                        $html .= "<div class='overlay'>";
                        $html .= "<div class='media-info'>";
                        $html .= "<div class='checkbox' style='margin-top: -3px !important'>";
                        $html .= "<input type='checkbox' id='metis-".$id."-selectimg" . $v->id . $additional . "'>";
                        $html .= "<label for='metis-".$id."-selectimg" . $v->id . $additional . "'></label>";
                        $html .= "</div>";
                        $html .= "<h2 style='margin-left:20px;'>" . Format::mb_substr($v->title, false, 0, 10) . "</h2>";
                        $html .= "</div>";
                        $html .= "</div>";
                        if($v->chapter && $v->charging_option){
                            $html .= "<div class='img-button'>";
                            $html .= "<a href='#video-play' cc_id=" . $v->chapter . " data-toggle='modal' class='btn btn-primary btn-sm margin-bottom-20 play'>播放</a> ";
                            $html .= "</div>";
                        }
                        $html .= "</div>";
                    }
                $html .= "</div>";
                $html .= "</div>";
                $html .= "</div>";
            }
        }
        return ($html) ? $html : Yii::t('backend', 'Data Is Empty');
    }
    
    public static function getValues($field, $value = false)
    {
        $values = [
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

    public function getAdmins()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id'])->alias('admins');
    }
}
