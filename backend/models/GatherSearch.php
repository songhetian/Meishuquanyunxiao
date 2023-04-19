<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Gather;
use common\models\Query;
/**
 * GatherSearch represents the model behind the search form of `app\models\Gather`.
 */
class GatherSearch extends Gather
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'activetime', 'admin_id', 'status'], 'integer'],
            [['name', 'course_material_id', 'author', 'introduction', 'created_at', 'updated_at', 'category_id'], 'safe'],
            [['price'], 'number'],
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
    public function search($params,$studio_id)
    {
        $query = Gather::find();

        // add conditions that should always apply here
        $query->joinWith(['authors']);
        $query->joinWith(['categorys']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
           # 'id' => $this->id,
            'activetime' => $this->activetime,
            'price' => $this->price,
            'gather.status' => Gather::STATUS_ACTIVE,
            'gather.studio_id' => $studio_id
        ]);

        $query->andFilterWhere(['like', 'gather.name', $this->name])
            ->andFilterWhere(['like', 'course_material_id', $this->course_material_id])
            ->andFilterWhere(['like', 'authors.name', $this->author])
            ->andFilterWhere(['like', 'categorys.name', $this->category_id])
            ->andFilterWhere(['like', 'introduction', $this->introduction]);

       $query = Query::andWhereTime($query, $this);
        return $dataProvider;
    }
}
