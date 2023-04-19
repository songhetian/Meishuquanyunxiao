<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Campus;
use common\models\Query;

/**
 * CampusSearch represents the model behind the search form about `common\models\Campus`.
 */
class CampusSearch extends Campus
{
    public function rules()
    {
        return [
            [['id', 'studio_id', 'is_main', 'created_at', 'updated_at', 'status'], 'integer'],
            [['name'], 'safe'],
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
        $query = Campus::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

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
            'id' => $this->id,
            'studio_id' => $this->studio_id,
            'is_main' => $this->is_main,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);
        
        $ids = Query::visible(Campus::className(), ['id']);
        if(is_array($ids)){
            $query->andFilterWhere([$this->tableName() . '.id' => $ids]);
        }

        $query->orderBy($this->tableName() . '.id DESC');
        
        return $dataProvider;
    }
}
