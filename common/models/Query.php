<?php

namespace common\models;

use Yii;
use yii\base\Model;
use common\models\Format;

class Query extends Model
{
    /**
     * [concatValue 多选字段拼接显示]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-31
     * @param     [type]        $table      [关联表]
     * @param     [type]        $value      [查询字段值]
     * @param     boolean       $reveal_all [是否显示所有]
     * @param     string        $field      [拼接字段]
     * @param     integer       $start      [截取起始点]
     * @param     integer       $len        [截取结束点]
     */
    static public function concatValue($table, $value, $reveal_all = false, $field = 'name', $start = 0, $len = 50){
        $model = $table::findAll(['id' => Format::explodeValue($value), 'status' => $table::STATUS_ACTIVE]);
        $name = [];
        foreach ($model as $v) {
            $name[] = $v->$field;
        }
        $name = Format::implodeValue($name);
        return Format::mb_substr($name, $reveal_all, $start, $len);
    }

    /**
     * [searchConcatValue description]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-31
     * @param     [type]        $query [当前表资源]
     * @param     [type]        $model [当前表]
     * @param     [type]        $table [目标表]
     * @param     [type]        $field [查询和目标字段]
     */
    static public function searchConcatValue($query, $model, $table, $field){
        $data = $query->all();
        
        foreach ($data as $value) {
            $name_str = '';
            $name = [];

            $exps = $table::findAll(['id' => Format::explodeValue($value->$field)]);
            foreach ($exps as $v) {
                $name[] = $v->name;
            }
            $name_str = Format::implodeValue($name);
            if(strstr($name_str, $model->$field)){
                $ids[] = $value->id;
            }
        }

        $ids = ($ids) ? $ids : 0;

        $query->andFilterWhere([$model->tableName() . '.id' => $ids]);

        return $query;
    }
    
    /**
     * [andWhereTime 添加时间条件]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-31
     */
	static public function andWhereTime($query, $model)
    {
        if ($model->created_at) {
            $query = self::whereTimeSql($query, $model, 'created_at');
        }
        if ($model->updated_at) {
            $query = self::whereTimeSql($query, $model, 'updated_at');
        }
        return $query;
	}

    /**
     * [concatErrors 拼接错误消息]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-31
     */
    static public function concatErrors($model)
    {
        foreach ($model->getErrors() as $value) {
            $message .= $value[0];
        }
        return $message;
    }
    
    /**
     * [visible 可见范围筛选]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-04-28
     */
    static public function visible($table, $fields = NULL, $where = NULL){
        $values = [
            Yii::$app->user->identity->campus_id,
            Yii::$app->user->identity->category_id,
            Yii::$app->user->identity->class_id
        ];

        $query = $table::find();
        for ($i = 0; $i < count($fields); $i++) {
            //对老师限制了可见范围 && 当前操作需要筛选数据
            if($values[$i] && $fields[$i]){
                //关联其他表
                if(strpos($fields[$i], '.') !== false){
                    $join = current(explode('.', $fields[$i]));
                    $query->joinWith([$join]);
                }
                //添加特殊条件
                if($where){
                    $query->andFilterWhere([$where, Format::concatField($fields[$i]), Format::concatString($values[$i])]);
                }else{
                    $query->andFilterWhere([$fields[$i] => Format::explodeValue($values[$i])]);
                }
            }
        }
         
        $model = $query->all();
        foreach ($model as $value) {
            $ids[] = $value->id;
        }
        return ($ids) ? $ids : [NULL];
    }

    /**
     * [whereTimeSql 计算时间区间]
     * @copyright [CraZyDoubLe]
     * @version   [v1.0]
     * @date      2017-03-31
     */
    static private function whereTimeSql($query, $model, $field)
    {
        $start = strtotime($model->$field);

        $end = $start + 24 * 3600;
        
        $field = $model->tableName() . "." . $field;
        $query->andFilterCompare($field, $start, '>=');
        $query->andFilterCompare($field, $end, '<=');

        return $query;
    }

}