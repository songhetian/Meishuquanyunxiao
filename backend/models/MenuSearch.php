<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Menu;

/**
 * MenuSearch represents the model behind the search form about `backend\models\Menu`.
 */
class MenuSearch extends Menu
{
    public $menu_name;
    public function rules()
    {
        return [
            [['id', 'pid', 'priority', 'created_at', 'updated_at', 'status'], 'integer'],
            [['name', 'route', 'icon', 'menu_name'], 'safe'],
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
        $query = Menu::find();

        // add conditions that should always apply here
        $query->joinWith(['menus']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        
        // 设置排序规则
        $dataProvider->sort->attributes['menu_name'] = [
            'asc' => ['menus.name' => SORT_ASC],
            'desc' => ['menus.name' => SORT_DESC]
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
            //'pid' => $this->pid,
            'priority' => $this->priority,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            $this->tableName() . '.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', $this->tableName() . '.name', $this->name])
            ->andFilterWhere(['like', 'route', $this->route])
            ->andFilterWhere(['like', 'icon', $this->icon])
            ->andFilterWhere(['like', 'menus.name', $this->menu_name]);

        $query->orderBy($this->tableName() . '.id DESC');
        
        return $dataProvider;
    }
}