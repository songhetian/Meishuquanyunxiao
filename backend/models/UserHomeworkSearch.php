<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserHomework;
use common\models\Query;
use common\models\Campus;
use common\models\Classes;
use common\models\Format;

/**
 * UserHomeworkSearch represents the model behind the search form about `common\models\UserHomework`.
 */
class UserHomeworkSearch extends UserHomework
{
    public $class_name;
    public $user_name;
    public $evaluator_name;
    public $course_material_name;

    public function rules()
    {
        return [
            [['id', 'user_id', 'course_material_id', 'evaluator', 'status'], 'integer'],
            [['image', 'comments', 'created_at', 'updated_at', 'class_name', 'user_name', 'evaluator_name', 'course_material_name'], 'safe'],
            [['score'], 'number'],
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
        $query = UserHomework::find();

        // add conditions that should always apply here
        $query->joinWith(['users']);
        $query->joinWith(['evaluators']);
        $query->joinWith(['courseMaterials']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        // 设置排序规则
        $dataProvider->sort->attributes['class_name'] = [
            'asc' => ['users.class_id' => SORT_ASC],
            'desc' => ['users.class_id' => SORT_DESC]
        ];
        $dataProvider->sort->attributes['user_name'] = [
            'asc' => ['users.name' => SORT_ASC],
            'desc' => ['users.name' => SORT_DESC]
        ];

        $dataProvider->sort->attributes['evaluator_name'] = [
            'asc' => ['evaluators.name' => SORT_ASC],
            'desc' => ['evaluators.name' => SORT_DESC]
        ];
        
        $dataProvider->sort->attributes['course_material_name'] = [
            'asc' => ['course_materials.name' => SORT_ASC],
            'desc' => ['course_materials.name' => SORT_DESC]
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        $this->status = $this::STATUS_ACTIVE;
        
        // grid filtering conditions
        $query->andFilterWhere([
            $this->tableName() . '.id' => $this->id,
            //'user_id' => $this->user_id,
            //'course_material_id' => $this->course_material_id,
            //'evaluator' => $this->evaluator,
            'score' => $this->score,
            //'created_at' => $this->created_at,
            //'updated_at' => $this->updated_at,
            $this->tableName() . '.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'comments', $this->comments])
            ->andFilterWhere(['like', 'users.name', $this->user_name])
            ->andFilterWhere(['like', 'evaluators.name', $this->evaluator_name])
            ->andFilterWhere(['like', 'course_materials.name', $this->course_material_name]);
        
        $ids = Query::visible(UserHomework::className(), ['users.campus_id', NULL, 'users.class_id']);
        if(is_array($ids)){
            $query->andFilterWhere([$this->tableName() . '.id' => $ids]);
        }

        if($this->class_name){
            $campus_id = Campus::getCampuses(Format::getStudio('id'));
            $classes = Classes::find()
                ->andFilterWhere(['campus_id' => $campus_id])
                ->andFilterWhere(['like', 'name', $this->class_name])
                ->all();
            foreach ($classes as $value) {
                $class_id[] = $value->id;
            }
            $query->andFilterWhere(['users.class_id' => $class_id]);
        }

        $query = Query::andWhereTime($query, $this);

        $query->orderBy($this->tableName() . '.id DESC');
        
        return $dataProvider;
    }
}
