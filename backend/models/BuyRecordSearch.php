<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\BuyRecord;

/**
 * BuyRecordSearch represents the model behind the search form of `common\models\BuyRecord`.
 */
class BuyRecordSearch extends BuyRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'buy_id', 'buy_studio', 'gather_id', 'gather_studio', 'created_at', 'updated_at', 'active_at', 'status'], 'integer'],
            [['price'], 'number'],
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
    public function search($params,$studio_id)
    {
        $query = BuyRecord::find();
        $query->where(['gather_studio'=>$studio_id]);
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
            'id' => $this->id,
            'buy_id' => $this->buy_id,
            'buy_studio' => $this->buy_studio,
            'gather_id' => $this->gather_id,
            'gather_studio' => $this->gather_studio,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'active_at' => $this->active_at,
            'price' => $this->price,
            'status' => $this->status,
        ]);

        return $dataProvider;
    }
}
