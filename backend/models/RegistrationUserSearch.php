<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\RegistrationUser;
use common\models\Format;
/**
 * RegistrationUserSearch represents the model behind the search form about `common\models\RegistrationUser`.
 */
class RegistrationUserSearch extends RegistrationUser
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['studio_id', 'user_id', 'timer', 'status'], 'integer'],
            [['user_type'], 'safe'],
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
        $query = RegistrationUser::find();

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
            'studio_id' => $this->studio_id,
            'user_id' => $this->user_id,
            'timer' => $this->timer,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'user_type', $this->user_type]);

        return $dataProvider;
    }
    

}
