<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ClassPeriod;
use common\models\Format;

/**
 * ClassPeriodSearch represents the model behind the search form about `common\models\ClassPeriod`.
 */
class ClassPeriodSearch extends ClassPeriod
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'studio_id', 'position', 'created_at', 'updated_at', 'status'], 'integer'],
            [['name', 'started_at', 'dismissed_at'], 'safe'],
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
        $query = ClassPeriod::find();

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

        $this->status = $this::STATUS_ACTIVE;
        $this->studio_id = Format::getStudio('id');
        
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'studio_id' => $this->studio_id,
            'position' => $this->position,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'started_at', $this->started_at])
            ->andFilterWhere(['like', 'dismissed_at', $this->dismissed_at]);

        $query->orderBy($this->tableName() . '.id DESC');
        
        return $dataProvider;
    }
}
