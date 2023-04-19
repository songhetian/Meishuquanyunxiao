<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Admin;
use common\models\CourseMaterial;
use common\models\Query;

/**
 * CourseMaterialSearch represents the model behind the search form about `common\models\CourseMaterial`.
 */
class CourseMaterialSearch extends CourseMaterial
{
    public $admin_name;
    public function rules()
    {
        return [
            [['id', 'admin_id', 'is_public', 'status'], 'integer'],
            [['name', 'description', 'created_at', 'updated_at', 'admin_name'], 'safe'],
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
    public function search($params,$ids='')
    {
        $query = CourseMaterial::find();

        $query->andFilterwhere(['course_material.id'=>$ids]);
        // add conditions that should always apply here
        $query->joinWith(['admins']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        // 设置排序规则
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
            $ids = Query::visible(CourseMaterial::className(), ['admins.campus_id', 'admins.category_id', 'admins.class_id'], 'or like');
            if(is_array($ids)){
                $query->andFilterWhere([$this->tableName() . '.id' => $ids]);
            }
        }else{
            $this->admin_id = Yii::$app->user->identity->id;
            $query->andFilterWhere(['admin_id' => $this->admin_id]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            $this->tableName() . '.id' => $this->id,
            //'admin_id' => $this->admin_id,
            'is_public' => $this->is_public,
            //'created_at' => $this->created_at,
            //'updated_at' => $this->updated_at,
            $this->tableName() . '.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', $this->tableName() . '.name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'admins.name', $this->admin_name]);

        $query = Query::andWhereTime($query, $this);

        $query->orderBy($this->tableName() . '.id DESC');
        
        return $dataProvider;
    }
}
