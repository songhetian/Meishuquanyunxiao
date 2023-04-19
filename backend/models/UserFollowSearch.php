<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserFollow;

/**
 * UserFollowSearch represents the model behind the search form about `common\models\UserFollow`.
 */
class UserFollowSearch extends UserFollow
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_follow_id', 'follow_user_id', 'timer', 'status', 'user_id', 'studio_id'], 'integer'],
            [['follow_user_type', 'user_type'], 'safe'],
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
        $query = UserFollow::find();

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
            'user_follow_id' => $this->user_follow_id,
            'follow_user_id' => $this->follow_user_id,
            'timer' => $this->timer,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'studio_id' => $this->studio_id,
        ]);

        $query->andFilterWhere(['like', 'follow_user_type', $this->follow_user_type])
            ->andFilterWhere(['like', 'user_type', $this->user_type]);

        return $dataProvider;
    }
}
