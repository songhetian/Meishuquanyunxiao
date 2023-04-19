<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\PrizeList;
use common\models\Format;

/**
 * PrizeListSearch represents the model behind the search form about `common\models\PrizeList`.
 */
class PrizeListSearch extends PrizeList
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['prize_list_id', 'created_at', 'updated_at', 'status', 'admin_id', 'studio_id', 'is_banner'], 'integer'],
            [['name', 'url', 'thumbnails', 'studio_name', 'desc'], 'safe'],
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
        $query = PrizeList::find();
        $query->joinWith(['admins']);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $studio_id = Format::getStudio('id');
        $query->andFilterWhere(['prize_list.studio_id' => $studio_id]);
        $query->andFilterWhere(['prize_list.status' => 10]);
        
        if(empty($params['sort'])){
            $query->orderBy(['prize_list_id' => SORT_DESC]);
        }
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'prize_list_id' => $this->prize_list_id,
            'prize_list.created_at' => $this->created_at,
            'prize_list.updated_at' => $this->updated_at,
            'prize_list.status' => $this->status,
            'prize_list.admin_id' => $this->admin_id,
            'prize_list.is_banner' => $this->is_banner,
        ]);

        $query->andFilterWhere(['like', 'prize_list.name', $this->name])
            ->andFilterWhere(['like', 'prize_list.url', $this->url])
            ->andFilterWhere(['like', 'prize_list.thumbnails', $this->thumbnails])
            ->andFilterWhere(['like', 'prize_list.studio_name', $this->studio_name])
            ->andFilterWhere(['like', 'prize_list.desc', $this->desc]);

        return $dataProvider;
    }
}
