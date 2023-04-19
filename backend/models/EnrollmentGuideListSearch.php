<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\EnrollmentGuideList;
use common\models\Format;

class EnrollmentGuideListSearch extends EnrollmentGuideList
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['enrollment_guide_list_id', 'created_at', 'updated_at', 'status', 'admin_id', 'studio_id', 'is_top', 'is_banner'], 'integer'],
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
        $query = EnrollmentGuideListSearch::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $studio_id = Format::getStudio('id');
        $query->andFilterWhere(['enrollment_guide_list.studio_id' => $studio_id]);
        $query->andFilterWhere(['enrollment_guide_list.status' => 10]);
        
        if(empty($params['sort'])){
            $query->orderBy(['enrollment_guide_list_id' => SORT_DESC]);
        }
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'enrollment_guide_list_id' => $this->enrollment_guide_list_id,
            'enrollment_guide_list.created_at' => $this->created_at,
            'enrollment_guide_list.updated_at' => $this->updated_at,
            'enrollment_guide_list.status' => $this->status,
            'enrollment_guide_list.admin_id' => $this->admin_id,
            'enrollment_guide_list.studio_id' => $this->studio_id,
            'enrollment_guide_list.is_top' => $this->is_top,
            'enrollment_guide_list.is_banner' => $this->is_banner,
        ]);

        $query->andFilterWhere(['like', 'enrollment_guide_list.name', $this->name])
            ->andFilterWhere(['like', 'enrollment_guide_list.url', $this->url])
            ->andFilterWhere(['like', 'enrollment_guide_list.thumbnails', $this->thumbnails])
            ->andFilterWhere(['like', 'enrollment_guide_list.desc', $this->desc]);

        return $dataProvider;
    }
}
