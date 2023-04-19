<?php

namespace teacher\modules\v2\models;

use common\models\Format;
use Yii;

class SimpleGather extends Gather
{
    public static $show = 0;

   # public static $version = 0;

    public function fields() {
        $fields = parent::fields();

        $fields['course_material_id'] = function() {
            return $this->course_material_id;
        };

        $fields['price'] = function() {

            return $this->price;
            // if(self::$version == Yii::$app->params['Version']){
            //     return 0;
            // }else{
            //     return $this->price;
            // }
        };
        return $fields;
    }

    /*
     *课件名称
     *
    */
    public static function GetMaterialList($course_material_id) {

        $ids = Format::explodeValue($course_material_id);

        $CourseMaterials = [];
        if(self::$show){
            foreach ($ids as $key => $value) {
               $CourseMaterials[] = SimpleCourseMaterial::findOne($value);
            }
        }else{
            CourseMaterial::$is_show = 0;
            foreach ($ids as $key => $value) {
               $CourseMaterials[] = CourseMaterial::findOne($value);
            }
        }
        return $CourseMaterials;
    }
}
