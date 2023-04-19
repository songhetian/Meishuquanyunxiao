<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Admin;
use common\models\Campus;
use common\models\Category;
use common\models\Classes;
use common\models\Query;

/**
 * AdminSearch represents the model behind the search form about `backend\models\Admin`.
 */
class AdminSearch extends Admin
{
    public $code_name;

    public function rules()
    {
        return [
            [['id', 'is_all_visible', 'is_main', 'status','is_sell'], 'integer'],
            [['phone_number', 'name', 'campus_id', 'category_id', 'class_id', 'auth_key', 'password_hash', 'password_reset_token', 'created_at', 'updated_at', 'code_name'], 'safe'],
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
    public function search($params, $pagesize = 20)
    {
        $query = Admin::find();

        // add conditions that should always apply here
        $query->joinWith(['codes']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pagesize,
            ],
        ]);
        
        $dataProvider->sort->attributes['code_name'] = [
            'asc' => ['codes.code' => SORT_ASC],
            'desc' => ['codes.code' => SORT_DESC]
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        if(!Yii::$app->user->can(Yii::$app->controller->id.'/recovery')){
           $this->status = $this::STATUS_ACTIVE;
        }
        
        // grid filtering conditions
        $query->andFilterWhere([
            $this->tableName() . '.id' => $this->id,
            //'created_at' => $this->created_at,
            //'updated_at' => $this->updated_at,
            'is_all_visible' => $this->is_all_visible,
            'is_main' => $this->is_main,
            $this->tableName() . '.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'phone_number', $this->phone_number])
            ->andFilterWhere(['like', $this->tableName() . '.name', $this->name])
            //->andFilterWhere(['like', 'campus_id', $this->campus_id])
            //->andFilterWhere(['like', 'category_id', $this->category_id])
            //->andFilterWhere(['like', 'class_id', $this->class_id])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'codes.code', $this->code_name]);

        if(!$this->id){
            if ($this->campus_id){
                $query = Query::searchConcatValue($query, $this, Campus::className(), 'campus_id');
            }
            if ($this->category_id){
                $query = Query::searchConcatValue($query, $this, Category::className(), 'category_id');
            }
            if ($this->class_id){
                $query = Query::searchConcatValue($query, $this, Classes::className(), 'class_id');
            }
        }

        if(Yii::$app->user->identity->is_all_visible == Admin::ALL_VISIBLE){
            $ids = Query::visible(Admin::className(), ['campus_id', 'category_id', 'class_id'], 'or like');
            if(is_array($ids)){
                $query->andFilterWhere([$this->tableName() . '.id' => $ids]);
            }
        }else{
            $query->andFilterWhere([$this->tableName() . '.id' => Yii::$app->user->identity->id]);
        }

        $query = Query::andWhereTime($query, $this);

        $query->orderBy($this->tableName() . '.id DESC');
        
        return $dataProvider;
    }
}