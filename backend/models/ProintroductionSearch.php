<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Prointroduction;
use common\models\Format;
/**
 * NewListSearch represents the model behind the search form about `common\models\NewList`.
 */
class ProintroductionSearch extends Prointroduction
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['prointroduction_id', 'created_at', 'updated_at', 'status', 'admin_id', 'studio_id', 'is_top', 'is_banner'], 'integer'],
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
        $query = Prointroduction::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $studio_id = Format::getStudio('id');
        $query->andFilterWhere(['prointroduction.studio_id' => $studio_id]);
        $query->andFilterWhere(['prointroduction.status' => 10]);
        
        if(empty($params['sort'])){
            $query->orderBy(['prointroduction.updated_at' => SORT_DESC]);
        }
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'prointroduction_id' => $this->prointroduction_id,
            'prointroduction.created_at' => $this->created_at,
            'prointroduction.updated_at' => $this->updated_at,
            'prointroduction.admin_id' => $this->admin_id,
            'prointroduction.studio_id' => $this->studio_id,
            'prointroduction.is_top' => $this->is_top,
            'prointroduction.is_banner' => $this->is_banner,
        ]);

        $query->andFilterWhere(['like', 'prointroduction.name', $this->name])
            ->andFilterWhere(['like', 'prointroduction.url', $this->url])
            ->andFilterWhere(['like', 'prointroduction.thumbnails', $this->thumbnails])
            ->andFilterWhere(['like', 'prointroduction.desc', $this->desc]);

        return $dataProvider;
    }
}
