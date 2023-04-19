<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\NewList;
use common\models\Format;
/**
 * NewListSearch represents the model behind the search form about `common\models\NewList`.
 */
class NewListSearch extends NewList
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['new_list_id', 'created_at', 'updated_at', 'status', 'admin_id', 'studio_id', 'is_top', 'is_banner'], 'integer'],
            [['name', 'url', 'thumbnails', 'desc'], 'safe'],
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
        $query = NewList::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $studio_id = Format::getStudio('id');
        $query->andFilterWhere(['new_list.studio_id' => $studio_id]);
        $query->andFilterWhere(['new_list.status' => 10]);
        
        if(empty($params['sort'])){
            $query->orderBy(['new_list.updated_at' => SORT_DESC]);
        }
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'new_list_id' => $this->new_list_id,
            'new_list.created_at' => $this->created_at,
            'new_list.updated_at' => $this->updated_at,
            'new_list.admin_id' => $this->admin_id,
            'new_list.studio_id' => $this->studio_id,
            'new_list.is_top' => $this->is_top,
            'new_list.is_banner' => $this->is_banner,
        ]);

        $query->andFilterWhere(['like', 'new_list.name', $this->name])
            ->andFilterWhere(['like', 'new_list.url', $this->url])
            ->andFilterWhere(['like', 'new_list.thumbnails', $this->thumbnails])
            ->andFilterWhere(['like', 'new_list.desc', $this->desc]);

        return $dataProvider;
    }
}
