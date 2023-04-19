<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Live;

/**
 * OpenadSearch represents the model behind the search form about `app\modules\admin\module\Openad`.
 */
class LiveSearch extends Live
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['live_id','user_id', 'status', 'connections', 'is_phone', 'play_status'], 'integer'],
            [['start_time', 'end_time'], 'safe'],
            [['live_id', 'title','cc_id'], 'string', 'max' => 200],
            [['description', 'pic_url'], 'string', 'max' => 300]
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
        $query = Live::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        if(!$params['sort']){
            $query->orderBy(['id'=>SORT_DESC]);
        }
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'connections' => $this->connections,
            'is_phone' => $this->is_phone,
            'description' => $this->description,
            'play_status' => $this->play_status,
        ]);

        $query->andFilterWhere(['like', 'cc_id', $this->cc_id])
            ->andFilterWhere(['like', 'live_id', $this->live_id])
            ->andFilterWhere(['like', 'pic_url', $this->pic_url])
            ->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
