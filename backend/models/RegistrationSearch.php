<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Registration;
use common\models\Format;
/**
 * RegistrationSearch represents the model behind the search form about `common\models\Registration`.
 */
class RegistrationSearch extends Registration
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['registration_id', 'studio_id', 'phone_number'], 'integer'],
            [['name', 'lat', 'lng', 'pic'], 'safe'],
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
        $query = Registration::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $studio_id = Format::getStudio('id');
        $query->andFilterWhere(['studio_id' => $studio_id]);
        if(empty($params['sort'])){
            $query->orderBy(['timer' => SORT_DESC]);
        }
        $this->load($params);


        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'registration_id' => $this->registration_id,
            'studio_id' => $this->studio_id,
            'phone_number' => $this->phone_number,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'lat', $this->lat])
            ->andFilterWhere(['like', 'lng', $this->lng])
            ->andFilterWhere(['like', 'pic', $this->pic]);

        return $dataProvider;
    }
}
