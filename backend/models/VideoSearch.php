<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Admin;
use common\models\Query;
use common\models\Video;
use common\models\Keyword;
use common\models\SouceGroup;

/**
 * VideoSearch represents the model behind the search form about `common\models\Video`.
 */
class VideoSearch extends Video
{
    public $category_name;
    public $admin_name;

    public function rules()
    {
        return [
            [['id', 'source', 'studio_id', 'instructor', 'category_id', 'watch_count', 'admin_id', 'is_public', 'status'], 'integer'],
            [['name', 'keyword_id', 'preview', 'cc_id', 'description', 'created_at', 'updated_at', 'category_name', 'admin_name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Video::find();

        // add conditions that should always apply here
        $query->joinWith(['categorys']);
        $query->joinWith(['admins']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        // 设置排序规则
        $dataProvider->sort->attributes['category_name'] = [
            'asc' => ['categorys.name' => SORT_ASC],
            'desc' => ['categorys.name' => SORT_DESC]
        ];
        $dataProvider->sort->attributes['admin_name'] = [
            'asc' => ['admins.name' => SORT_ASC],
            'desc' => ['admins.name' => SORT_DESC]
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        $gid = Yii::$app->session->get('gid');
        if($gid){
            $model = SouceGroup::findOne($gid);
            if($model->material_library_id){
                $ids = explode(',', $model->material_library_id);
                $query->andFilterWhere([$this->tableName() . '.id' => $ids]);
            }else{
                $query->andFilterWhere([$this->tableName() . '.id' => 0]);
            }
        }

        //$this->source = $this::SOURCE_LOCAL;
        
        if(!Yii::$app->user->can(Yii::$app->controller->id.'/display-delete')) {
           $this->status = $this::STATUS_ACTIVE;
        }

        /*
        if(Yii::$app->user->identity->is_all_visible == Admin::ALL_VISIBLE){
            $ids = Query::visible(Video::className(), ['admins.campus_id', 'admins.category_id', 'admins.class_id'], 'or like');
            if(is_array($ids)){
                $query->andFilterWhere([$this->tableName() . '.id' => $ids]);
            }
        }else{
            $this->admin_id = Yii::$app->user->identity->id;
            $query->andFilterWhere(['admin_id' => $this->admin_id]);
        }
        */
       
        $this->admin_id = Yii::$app->user->identity->id;
        $query->andFilterWhere([$this->tableName() . '.admin_id' => $this->admin_id]);
        $query->andFilterWhere([$this->tableName() . '.status' => Video::STATUS_ACTIVE]);
        
        // grid filtering conditions
        $query->andFilterWhere([
            $this->tableName() . '.id' => $this->id,
            'source' => $this->source,
            'studio_id' => $this->studio_id,
            'instructor' => $this->instructor,
            //'category_id' => $this->category_id,
            'watch_count' => $this->watch_count,
            //'admin_id' => $this->admin_id,
            'is_public' => $this->is_public,
            //'created_at' => $this->created_at,
            //'updated_at' => $this->updated_at,
            $this->tableName() . '.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', $this->tableName() . '.name', $this->name])
            //->andFilterWhere(['like', 'keyword_id', $this->keyword_id])
            ->andFilterWhere(['like', 'preview', $this->preview])
            ->andFilterWhere(['like', 'cc_id', $this->cc_id])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'categorys.name', $this->category_name])
            ->andFilterWhere(['like', 'admins.name', $this->admin_name]);

        if ($this->keyword_id && !$this->id){
            $query = Query::searchConcatValue($query, $this, Keyword::className(), 'keyword_id');
        }

        $query = Query::andWhereTime($query, $this);

        $query->orderBy($this->tableName() . '.id DESC');
        
        return $dataProvider;
    }
}
