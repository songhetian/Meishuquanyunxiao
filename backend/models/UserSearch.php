<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Admin;
use common\models\User;
use common\models\Query;

/**
 * UserSearch represents the model behind the search form about `\common\models\User`.
 */
class UserSearch extends User
{
    public $campus_name;
    public $class_name;
    public $race_name;
    public $province_name;
    public $city_name;
    public $united_exam_province_name;
    public $admin_name;
    public $code_name;
    
    public function rules()
    {
        return [
            [['id', 'student_id', 'campus_id', 'class_id', 'gender', 'relationship', 'race', 'student_type', 'career_pursuit_type', 'residence_type', 'grade', 'province', 'city', 'united_exam_province', 'is_graduation', 'graduation_at', 'is_all_visible', 'admin_id', 'is_review', 'status'], 'integer'],
            [['name', 'national_id', 'family_member_name', 'organization', 'position', 'contact_phone', 'detailed_address', 'qq_number', 'phone_number', 'school_name', 'fine_art_instructor', 'exam_participant_number', 'pre_school_assessment', 'note', 'auth_key', 'password_hash', 'password_reset_token', 'device_token', 'access_token', 'created_at', 'updated_at', 'campus_name', 'class_name', 'race_name', 'province_name', 'city_name', 'united_exam_province_name', 'admin_name', 'code_name'], 'safe'],
            [['sketch_score', 'color_score', 'quick_sketch_score', 'design_score', 'verbal_score', 'math_score', 'english_score', 'total_score'], 'number'],
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
        $query = User::find();

        // add conditions that should always apply here
        $query->joinWith(['campuses']);
        $query->joinWith(['classes']);
        $query->joinWith(['races']);
        $query->joinWith(['provinces']);
        $query->joinWith(['citys']);
        $query->joinWith(['unitedExamProvinces']);
        $query->joinWith(['admins']);
        $query->joinWith(['codes']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pagesize,
            ],
        ]);

        // 设置排序规则
        $dataProvider->sort->attributes['campus_name'] = [
            'asc' => ['campuses.name' => SORT_ASC],
            'desc' => ['campuses.name' => SORT_DESC]
        ];

        $dataProvider->sort->attributes['class_name'] = [
            'asc' => ['classes.name' => SORT_ASC],
            'desc' => ['classes.name' => SORT_DESC]
        ];
        
        $dataProvider->sort->attributes['race_name'] = [
            'asc' => ['races.name' => SORT_ASC],
            'desc' => ['races.name' => SORT_DESC]
        ];

        $dataProvider->sort->attributes['province_name'] = [
            'asc' => ['provinces.name' => SORT_ASC],
            'desc' => ['provinces.name' => SORT_DESC]
        ];

        $dataProvider->sort->attributes['city_name'] = [
            'asc' => ['citys.name' => SORT_ASC],
            'desc' => ['citys.name' => SORT_DESC]
        ];

        $dataProvider->sort->attributes['united_exam_province_name'] = [
            'asc' => ['united_exam_provinces.name' => SORT_ASC],
            'desc' => ['united_exam_provinces.name' => SORT_DESC]
        ];

        $dataProvider->sort->attributes['admin_name'] = [
            'asc' => ['admins.name' => SORT_ASC],
            'desc' => ['admins.name' => SORT_DESC]
        ];

        $dataProvider->sort->attributes['code_name'] = [
            'asc' => ['codes.code' => SORT_ASC],
            'desc' => ['codes.code' => SORT_DESC]
        ];
        
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        //if(!Yii::$app->user->can(Yii::$app->controller->id.'/recovery')){
            $this->status = $this::STATUS_ACTIVE;
        //}

        // grid filtering conditions
        $query->andFilterWhere([
            $this->tableName() . '.id' => $this->id,
            'student_id' => $this->student_id,
            'gender' => $this->gender,
            'relationship' => $this->relationship,
            //'race' => $this->race,
            'student_type' => $this->student_type,
            'career_pursuit_type' => $this->career_pursuit_type,
            'residence_type' => $this->residence_type,
            'grade' => $this->grade,
            //'province' => $this->province,
            //'city' => $this->city,
            //'united_exam_province' => $this->united_exam_province,
            'sketch_score' => $this->sketch_score,
            'color_score' => $this->color_score,
            'quick_sketch_score' => $this->quick_sketch_score,
            'design_score' => $this->design_score,
            'verbal_score' => $this->verbal_score,
            'math_score' => $this->math_score,
            'english_score' => $this->english_score,
            'total_score' => $this->total_score,
            'is_graduation' => $this->is_graduation,
            'graduation_at' => $this->graduation_at,
            //'campus_id' => $this->campus_id,
            //'class_id' => $this->class_id,
            //'created_at' => $this->created_at,
            //'updated_at' => $this->updated_at,
            'user.is_all_visible' => $this->is_all_visible,
            //'admin_id' => $this->admin_id,
            'is_review' => $this->is_review,
            $this->tableName() . '.status' => $this->status,
        ]);
        $query->andFilterWhere(['like', $this->tableName() . '.name', $this->name])
            ->andFilterWhere(['like', 'national_id', $this->national_id])
            ->andFilterWhere(['like', 'family_member_name', $this->family_member_name])
            ->andFilterWhere(['like', 'organization', $this->organization])
            ->andFilterWhere(['like', 'position', $this->position])
            ->andFilterWhere(['like', 'contact_phone', $this->contact_phone])
            ->andFilterWhere(['like', 'detailed_address', $this->detailed_address])
            ->andFilterWhere(['like', 'qq_number', $this->qq_number])
            ->andFilterWhere(['like', $this->tableName() . '.phone_number', $this->phone_number])
            ->andFilterWhere(['like', 'school_name', $this->school_name])
            ->andFilterWhere(['like', 'fine_art_instructor', $this->fine_art_instructor])
            ->andFilterWhere(['like', 'exam_participant_number', $this->exam_participant_number])
            ->andFilterWhere(['like', 'pre_school_assessment', $this->pre_school_assessment])
            ->andFilterWhere(['like', 'note', $this->note])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'device_token', $this->device_token])
            ->andFilterWhere(['like', 'access_token', $this->access_token])
            ->andFilterWhere(['like', 'campuses.name', $this->campus_name])
            ->andFilterWhere(['like', 'classes.name', $this->class_name])
            ->andFilterWhere(['like', 'races.name', $this->race_name])
            ->andFilterWhere(['like', 'provinces.name', $this->province_name])
            ->andFilterWhere(['like', 'citys.name', $this->city_name])
            ->andFilterWhere(['like', 'united_exam_provinces.name', $this->united_exam_province_name])
            ->andFilterWhere(['like', 'admins.name', $this->admin_name])
            ->andFilterWhere(['like', 'codes.code', $this->code_name]); 
        
        $ids = Query::visible(User::className(), ['campus_id', NULL, 'class_id']);
        if(is_array($ids)){
            $query->andFilterWhere([$this->tableName() . '.id' => $ids]);
        }

        $query = Query::andWhereTime($query, $this);

        $query->orderBy($this->tableName() . '.id DESC');
        
        return $dataProvider;
    }
}