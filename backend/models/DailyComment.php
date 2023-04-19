<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\DailyComment as DailyCommentModel;

/**
 * DailyComment represents the model behind the search form about `common\models\DailyComment`.
 */
class DailyComment extends DailyCommentModel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['daily_comment_id', 'daily_id', 'daily_comment_pid', 'timer', 'status', 'user_id', 'studio_id','reply_user_id'], 'integer'],
            [['content', 'user_type'], 'safe'],
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
        $query = DailyCommentModel::find();

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
            'daily_comment_id' => $this->daily_comment_id,
            'daily_id' => $this->daily_id,
            'daily_comment_pid' => $this->daily_comment_pid,
            'timer' => $this->timer,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'studio_id' => $this->studio_id,
        ]);

        $query->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'user_type', $this->user_type]);

        return $dataProvider;
    }
}
