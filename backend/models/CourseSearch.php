<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Course;
use common\models\Query;
use backend\models\Admin;

/**
 * CourseSearch represents the model behind the search form about `common\models\Course`.
 */
class CourseSearch extends Course
{
    public $class_period_name;
    public $class_name;
    public $category_name;
    public $instructor_name;
    public $instruction_method_name;
    public $course_material_name;
    public $admin_name;

    public function rules()
    {
        return [
            [['id', 'class_period_id', 'class_id', 'category_id', 'instructor', 'instruction_method_id', 'course_material_id', 'admin_id', 'status'], 'integer'],
            [['class_content', 'class_emphasis', 'note', 'started_at', 'ended_at', 'created_at', 'updated_at', 'class_period_name', 'class_name', 'category_name', 'instructor_name', 'instruction_method_name', 'course_material_name', 'admin_name'], 'safe'],
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
    public function search($params, $pagesize = 20)
    {
        $query = Course::find();

        // add conditions that should always apply here
        $query->joinWith(['classPeriods']);
        $query->joinWith(['classes']);
        $query->joinWith(['categorys']);
        $query->joinWith(['instructors']);
        $query->joinWith(['instructionMethods']);
        $query->joinWith(['courseMaterials']);
        $query->joinWith(['admins']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pagesize,
            ],
        ]);

        // 设置排序规则
        $dataProvider->sort->attributes['class_period_name'] = [
            'asc' => ['class_periods.name' => SORT_ASC],
            'desc' => ['class_periods.name' => SORT_DESC]
        ];
        $dataProvider->sort->attributes['class_name'] = [
            'asc' => ['classes.name' => SORT_ASC],
            'desc' => ['classes.name' => SORT_DESC]
        ];
        $dataProvider->sort->attributes['category_name'] = [
            'asc' => ['categorys.name' => SORT_ASC],
            'desc' => ['categorys.name' => SORT_DESC]
        ];
        $dataProvider->sort->attributes['instructor_name'] = [
            'asc' => ['instructors.name' => SORT_ASC],
            'desc' => ['instructors.name' => SORT_DESC]
        ];
        $dataProvider->sort->attributes['instruction_method_name'] = [
            'asc' => ['instruction_methods.name' => SORT_ASC],
            'desc' => ['instruction_methods.name' => SORT_DESC]
        ];
        $dataProvider->sort->attributes['course_material_name'] = [
            'asc' => ['course_materials.name' => SORT_ASC],
            'desc' => ['course_materials.name' => SORT_DESC]
        ];
        $dataProvider->sort->attributes['admin_name'] = [
            'asc' => ['admins.name' => SORT_ASC],
            'desc' => ['admins.name' => SORT_DESC]
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

        if(Yii::$app->user->identity->is_all_visible == Admin::ALL_VISIBLE){
            $ids = Query::visible(Course::className(), ['classes.campus_id', 'category_id', 'class_id']);
            if(is_array($ids)){
                $query->andFilterWhere([$this->tableName() . '.id' => $ids]);
            }
        }else{
            $this->instructor = Yii::$app->user->identity->id;
            $query->andFilterWhere(['instructor' => $this->instructor]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            $this->tableName() . '.id' => $this->id,
            //'class_period_id' => $this->class_period_id,
            //'class_id' => $this->class_id,
            //'category_id' => $this->category_id,
            //'instructor' => $this->instructor,
            //'instruction_method_id' => $this->instruction_method_id,
            'course_material_id' => $this->course_material_id,
            //'started_at' => $this->started_at,
            //'ended_at' => $this->ended_at,
            //'admin_id' => $this->admin_id,
            //'created_at' => $this->created_at,
            //'updated_at' => $this->updated_at,
            $this->tableName() . '.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'class_content', $this->class_content])
            ->andFilterWhere(['like', 'class_emphasis', $this->class_emphasis])
            ->andFilterWhere(['like', 'note', $this->note])
            ->andFilterWhere(['like', 'class_periods.name', $this->class_period_name])
            ->andFilterWhere(['like', 'classes.name', $this->class_name])
            ->andFilterWhere(['like', 'categorys.name', $this->category_name])
            ->andFilterWhere(['like', 'instructors.name', $this->instructor_name])
            ->andFilterWhere(['like', 'instruction_methods.name', $this->instruction_method_name])
            ->andFilterWhere(['like', 'course_materials.name', $this->course_material_name])
            ->andFilterWhere(['like', 'admins.name', $this->admin_name]);

        $query = Course::getDateType($query, $this->started_at);
        $query = Query::andWhereTime($query, $this);

        $query->orderBy($this->tableName() . '.id DESC');

        return $dataProvider;
    }
}
