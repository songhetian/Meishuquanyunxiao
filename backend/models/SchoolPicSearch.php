<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Format;
use common\models\SchoolPic;

/**
 * SchoolPicSearch represents the model behind the search form about `common\models\SchoolPic`.
 */
class SchoolPicSearch extends SchoolPic
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['school_pic_id', 'created_at', 'updated_at', 'status', 'admin_id', 'studio_id'], 'integer'],
            [['pic_url', 'desc', 'type'], 'safe'],
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
        $query = SchoolPic::find();
        $query->joinWith(['admins']);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $studio_id = Format::getStudio('id');
        $query->andFilterWhere(['school_pic.studio_id' => $studio_id]);
        $query->andFilterWhere(['school_pic.status' => 10]);

        $this->load($params);
        if(empty($params['sort'])){
            $query->orderBy(['school_pic_id' => SORT_ASC]);
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'school_pic_id' => $this->school_pic_id,
            'school_pic.created_at' => $this->created_at,
            'school_pic.updated_at' => $this->updated_at,
            'school_pic.admin_id' => $this->admin_id,
            'school_pic.studio_id' => $this->studio_id,
        ]);

        $query->andFilterWhere(['like', 'school_pic.pic_url', $this->pic_url])
            ->andFilterWhere(['like', 'school_pic.desc', $this->desc])
            ->andFilterWhere(['like', 'school_pic.type', $this->type]);

        return $dataProvider;
    }
}
