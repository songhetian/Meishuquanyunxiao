<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\DormitoryList;
use common\models\Format;
/**
 * DormitoryListSearch represents the model behind the search form about `common\models\DormitoryList`.
 */
class DormitoryListSearch extends DormitoryList
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dormitory_list_id', 'created_at', 'updated_at', 'status', 'admin_id', 'studio_id', 'bed_num'], 'integer'],
            [['pic_url', 'name'], 'safe'],
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
        $query = DormitoryList::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $studio_id = Format::getStudio('id');
        $query->andFilterWhere(['studio_id' => $studio_id]);
        
        if(empty($params['sort'])){
            $query->orderBy(['dormitory_list_id' => SORT_DESC]);
        }
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'dormitory_list_id' => $this->dormitory_list_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->status,
            'admin_id' => $this->admin_id,
            'studio_id' => $this->studio_id,
            'bed_num' => $this->bed_num,
        ]);

        $query->andFilterWhere(['like', 'pic_url', $this->pic_url])
            ->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
