<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SouceGroup;

/**
 * SouceGroupSearch represents the model behind the search form about `common\models\SouceGroup`.
 */
class SouceGroupSearch extends SouceGroup
{
    public function rules()
    {
        return [
            [['id', 'admin_id', 'role', 'is_main', 'is_public', 'type', 'created_at', 'updated_at', 'status'], 'integer'],
            [['name', 'material_library_id'], 'safe'],
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
        $query = SouceGroup::find()->where(['type' => Yii::$app->session->get('type')]);

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $this->admin_id = Yii::$app->user->identity->id;
        $query->andFilterWhere(['admin_id' => $this->admin_id]);
        $query->andFilterWhere(['status' => SouceGroup::STATUS_ACTIVE]);
        
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'admin_id' => $this->admin_id,
            'role' => $this->role,
            'is_main' => $this->is_main,
            'is_public' => $this->is_public,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'material_library_id', $this->material_library_id]);

        $query->orderBy($this->tableName() . '.id DESC');


        return $dataProvider;
    }
}