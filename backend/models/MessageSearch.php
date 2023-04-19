<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Message;
use common\models\Campus;
use common\models\Category;
use common\models\Classes;
use common\models\Query;
/**
 * MessageSearch represents the model behind the search form about `common\models\Message`.
 */
class MessageSearch extends Message
{
    public $message_category_name;
    public $user_name;
    public $admin_name;

    public function rules()
    {
        return [
            [['id', 'message_category_id', 'user_id', 'correlated_id', 'code', 'admin_id','status'], 'integer'],
            [['campus_id', 'category_id', 'class_id', 'title', 'content', 'created_at', 'updated_at', 'message_category_name', 'user_name', 'admin_name'], 'safe'],
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
        $query = Message::find();

        // add conditions that should always apply here
        $query->joinWith(['messageCategorys']);
        $query->joinWith(['users']);
        $query->joinWith(['admins']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        // 设置排序规则
        $dataProvider->sort->attributes['message_category_name'] = [
            'asc' => ['message_categorys.name' => SORT_ASC],
            'desc' => ['message_categorys.name' => SORT_DESC]
        ];

        $dataProvider->sort->attributes['user_name'] = [
            'asc' => ['users.name' => SORT_ASC],
            'desc' => ['users.name' => SORT_DESC]
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

        $this->status = $this::STATUS_ACTIVE;

        // grid filtering conditions
        $query->andFilterWhere([
            $this->tableName() . '.id' => $this->id,
            //'message_category_id' => $this->message_category_id,
            //'user_id' => $this->user_id,
            'correlated_id' => $this->correlated_id,
            'code' => $this->code,
            //'admin_id' => $this->admin_id,
            //'created_at' => $this->created_at,
            //'updated_at' => $this->updated_at,
            $this->tableName() . '.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            //->andFilterWhere(['like', 'campus_id', $this->campus_id])
            //->andFilterWhere(['like', 'category_id', $this->category_id])
            //->andFilterWhere(['like', 'class_id', $this->class_id])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'message_categorys.name', $this->message_category_name])
            ->andFilterWhere(['like', 'users.name', $this->user_name])
            ->andFilterWhere(['like', 'admins.name', $this->admin_name]);

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
        
        $ids = Query::visible(Message::className(), ['campus_id', 'category_id', 'class_id'], 'or like');
        if(is_array($ids)){
            $query->andFilterWhere([$this->tableName() . '.id' => $ids]);
        }

        $query = Query::andWhereTime($query, $this);

        $query->orderBy($this->tableName() . '.id DESC');
        
        return $dataProvider;
    }
}
