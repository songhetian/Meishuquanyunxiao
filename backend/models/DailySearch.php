<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Daily;

/**
 * DailySearch represents the model behind the search form about `common\models\Daily`.
 */
class DailySearch extends Daily
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['daily_id', 'timer', 'status', 'user_id', 'studio_id', 'views'], 'integer'],
            [['name', 'avatar', 'image_url_came', 'content', 'user_type'], 'safe'],
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
        $query = Daily::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'daily_id' => $this->daily_id,
            'timer' => $this->timer,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'studio_id' => $this->studio_id,
            'views' => $this->views,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'avatar', $this->avatar])
            ->andFilterWhere(['like', 'image_url_came', $this->image_url_came])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'user_type', $this->user_type]);

        return $dataProvider;
    }
}
