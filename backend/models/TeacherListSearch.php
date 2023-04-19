<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Format;
use common\models\TeacherList;

/**
 * TeacherListSearch represents the model behind the search form about `common\models\TeacherList`.
 */
class TeacherListSearch extends TeacherList
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['teacher_list_id', 'created_at', 'updated_at', 'status', 'admin_id', 'studio_id'], 'integer'],
            [['name', 'pic_url', 'desc','auth'], 'safe'],
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
        $query = TeacherList::find();
        $query->joinWith(['admins']);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $studio_id = Format::getStudio('id');
        $query->andFilterWhere(['teacher_list.studio_id' => $studio_id]);
        $query->andFilterWhere(['teacher_list.status' => 10]);
        $this->load($params);
        if(empty($params['sort'])){
            $query->orderBy(['teacher_list_id' => SORT_ASC]);
        }
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'teacher_list_id' => $this->teacher_list_id,
            'teacher_list.created_at' => $this->created_at,
            'teacher_list.updated_at' => $this->updated_at,
            'teacher_list.status' => $this->status,
            'teacher_list.admin_id' => $this->admin_id,
            'teacher_list.studio_id' => $this->studio_id,
        ]);

        $query->andFilterWhere(['like', 'teacher_list.name', $this->name])
            ->andFilterWhere(['like', 'teacher_list.auth', $this->auth])
            ->andFilterWhere(['like', 'teacher_list.pic_url', $this->pic_url])
            ->andFilterWhere(['like', 'teacher_list.desc', $this->desc]);

        return $dataProvider;
    }
}
