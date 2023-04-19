<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserLike;

/**
 * UserLikeSearch represents the model behind the search form about `common\models\UserLike`.
 */
class UserLikeSearch extends UserLike
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_like_id', 'like_id', 'timer', 'status', 'user_id', 'studio_id'], 'integer'],
            [['like_type', 'user_type'], 'safe'],
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
        $query = UserLike::find();

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
            'user_like_id' => $this->user_like_id,
            'like_id' => $this->like_id,
            'timer' => $this->timer,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'studio_id' => $this->studio_id,
        ]);

        $query->andFilterWhere(['like', 'like_type', $this->like_type])
            ->andFilterWhere(['like', 'user_type', $this->user_type]);

        return $dataProvider;
    }
}
