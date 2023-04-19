<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Rbac;
use common\models\Format;

/**
 * RbacSearch represents the model behind the search form about `backend\models\Rbac`.
 */
class RbacSearch extends Rbac
{
    public function rules()
    {
        return [
            [['pid', 'studio_id', 'type', 'created_at', 'updated_at', 'status'], 'integer'],
            [['name', 'scope', 'description', 'rule_name', 'data'], 'safe']
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
    public function search($params, $type)
    {
        $query = Rbac::find()->where(['type' => $type]);

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        /*
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        */
        if($this->pid){
            $ids = Rbac::getParents($this->pid);
            if($ids){
                $query->andFilterWhere(['pid' => $ids]);
            }else{
                $query->where('0=1');
                return $dataProvider;
            }
        }
        $this->status = $this::STATUS_ACTIVE;
        $this->studio_id = Format::getStudio('id');
        
        // grid filtering conditions
        $query->andFilterWhere([
            //'pid' => $this->pid,
            'studio_id' => $this->studio_id,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'scope', $this->scope])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'rule_name', $this->rule_name])
            ->andFilterWhere(['like', 'data', $this->data]);

        return $dataProvider;
    }
}