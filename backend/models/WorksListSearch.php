<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Format;
use common\models\WorksList;

/**
 * WorksListSearch represents the model behind the search form about `common\models\WorksList`.
 */
class WorksListSearch extends WorksList
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['works_list_id', 'created_at', 'updated_at', 'status', 'admin_id', 'studio_id','is_teacher'], 'integer'],
            [['name', 'pic_url', 'desc', 'type'], 'safe'],
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
        $query = WorksList::find();
        $query->joinWith(['admins']);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $studio_id = Format::getStudio('id');
        $query->andFilterWhere(['works_list.studio_id' => $studio_id]);
        $query->andFilterWhere(['works_list.status' => 10]);

        $this->load($params);
        if(empty($params['sort'])){
            $query->orderBy(['works_list_id' => SORT_DESC]);
        }
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'works_list_id' => $this->works_list_id,
            'works_list.created_at' => $this->created_at,
            'works_list.updated_at' => $this->updated_at,
            'works_list.status' => $this->status,
            'works_list.admin_id' => $this->admin_id,
            'works_list.studio_id' => $this->studio_id,
            'works_list.is_teacher' => $this->is_teacher,
        ]);

        $query->andFilterWhere(['like', 'works_list.name', $this->name])
            ->andFilterWhere(['like', 'works_list.pic_url', $this->pic_url])
            ->andFilterWhere(['like', 'works_list.desc', $this->desc])
            ->andFilterWhere(['like', 'type', $this->type]);

        return $dataProvider;
    }
}
