<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\CcLive;

/**
 * OpenadSearch represents the model behind the search form about `app\modules\admin\module\Openad`.
 */
class CcLiveSearch extends CcLive
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'templatetype','play_status', 'authtype', 'playpass', 'barrage', 'foreignpublish', 'openlowdelaymode', 'showusercount', 'status', 'is_recommend'], 'integer'],
            [['user_id'], 'required'],
            [['create_time', 'start_time','end_time'], 'safe'],
            [['title', 'description', 'publisherpass', 'assistantpass', 'cc_id', 'checkurl', 'publish_url', 'pic_url'], 'string', 'max' => 200]
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
        $query = CcLive::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        if(!$params['sort']){
            $query->orderBy(['id' => SORT_DESC]);
        }
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'cc_id' => $this->cc_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'is_recommend' => $this->is_recommend,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'create_time' => $this->create_time,
            'play_status' => $this->play_status,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'pic_url', $this->pic_url])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'share_link', $this->share_link]);

        return $dataProvider;
    }
}
