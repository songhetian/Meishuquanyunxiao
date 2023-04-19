<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Classes;
use common\models\Query;

/**
 * ClassesSearch represents the model behind the search form about `common\models\Classes`.
 */
class ClassesSearch extends Classes
{
    public $campus_name;
    public $supervisor_name;
    public function rules()
    {
        return [
            [['id', 'year', 'campus_id', 'supervisor', 'created_at', 'updated_at', 'status'], 'integer'],
            [['name', 'note', 'campus_name', 'supervisor_name'], 'safe'],
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
        $query = Classes::find();

        $query->joinWith(['campuses']);
        $query->joinWith(['supervisors']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        // 设置排序规则
        $dataProvider->sort->attributes['campus_name'] = [
            'asc' => ['campuses.name' => SORT_ASC],
            'desc' => ['campuses.name' => SORT_DESC]
        ];
        $dataProvider->sort->attributes['supervisor_name'] = [
            'asc' => ['supervisors.name' => SORT_ASC],
            'desc' => ['supervisors.name' => SORT_DESC]
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if(!Yii::$app->user->can(Yii::$app->controller->id.'/recovery')){
            $this->status = $this::STATUS_ACTIVE;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            $this->tableName() . '.id' => $this->id,
            'year' => $this->year,
            'campus_id' => $this->campus_id,
            'supervisor' => $this->supervisor,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            $this->tableName() . '.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', $this->tableName() . '.name', $this->name])
            ->andFilterWhere(['like', 'note', $this->note])
            ->andFilterWhere(['like', 'campuses.name', $this->campus_name])
            ->andFilterWhere(['like', 'supervisors.name', $this->supervisor_name]);

        $ids = Query::visible(Classes::className(), ['campus_id', NULL, 'id']);
        if(is_array($ids)){
            $query->andFilterWhere([$this->tableName() . '.id' => $ids]);
        }
        
        $query->orderBy($this->tableName() . '.id DESC');
        
        return $dataProvider;
    }
}
