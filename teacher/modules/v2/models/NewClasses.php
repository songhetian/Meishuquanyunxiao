<?php 
	
namespace teacher\modules\v2\models;

use Yii;
use components\Oss;
use common\models\Format;
use common\models\BuyClasses;

class NewClasses extends \common\models\Classes
{   
    public static $visitor_id;
    public static $role;
	public function fields()
	{
	    $fields = parent::fields();

	    $fields['class_id'] = function() {
	    	return $this->id;
	    };

        if($this->supervisor){
            $fields['supervisor'] = function () {
                return $this->supervisors->name;
            };
        }

        $fields['image']      = function () {

            if($this->image) {

                $size        = Yii::$app->params['oss']['Size']['950x540'];
                
                $studio      = Campus::findOne($this->campus_id)->studio_id;

                return Oss::getUrl($studio, 'picture', 'image', $this->image).$size;

            }else{
                return "https://api.teacher.meishuquanyunxiao.com/icon/bg.jpg";
            }
        };
        $fields['user_count'] = function () {
            return User::find()->where(['class_id' => $this->id,'status'=>10])->count();
        };

        $fields['price'] = function () {
            return array(
                    'price_id' => $this->price,
                    'info'     => Yii::$app->params['AndroidPrice'][$this->price].'元'
            );
        };

        $fields['ios_price'] = function () {
            return array(
                    'price'    => $this->price,
                    'price_id' => "com.meishuquanyunxiao.artworld.courseware".Yii::$app->params['IosPrice'][$this->price],
                    'info'     => "￥".Yii::$app->params['IosPrice'][$this->price]
            );
            
        };

        $fields['phone_number'] = function () {
            return $this->supervisors->phone_number;
        };

        $fields['tip'] = function () {

            if($this->tip) {
                return array(
                        'tip_id' => $this->tips->id,
                        'title'  => $this->tips->title
                );
            }else{
                return array(
                        'tip_id' => 0,
                        'title'  => "无优惠"
                );
            }
        };
        $fields['is_buy']    = function () {
        	//判断是否购买
        	if($this->type == 2) {
                if(self::$role == 10) {
                    if(in_array(self::$visitor_id, array($this->supervisor,$this->assistant,$this->lecturer))) {
                        return true;
                    }else{
                        return BuyClasses::getBuyStatus(self::$visitor_id,self::$role,$this->id);
                    }  
                }else{
                    return BuyClasses::getBuyStatus(self::$visitor_id,self::$role,$this->id);
                }
        	}

        	return true;
        };
	    unset(
	    	$fields['created_at'],
	    	$fields['updated_at'],
           # $fields['type'],
            $fields['year'],
            $fields['note'],
            $fields['campus_id'],
            $fields['supervisor'],
            $fields['status']
	    );
	    return $fields;
	}

    /**
     * @Author    田鹤松
     * @DateTime  2021-01-19
     * @copyright [获取可编辑列表]
     * @license   [license]
     * @version   [version]
     * @param     [type]      $course_material_id [课件id
     * @return    [type]                          [description]
     */
    public static function getCourseAdminList($course_material_id) {
        //使用该课件的班级
        $classes  =  \common\models\Course::find() 
                               ->select('class_id')
                               ->where(['course_material_id'=>$course_material_id])
                               ->asArray()
                               ->all();

        $list =  array_unique(array_column($classes, 'class_id'));

        $TeacherList=  \common\models\Classes::find()
                                     ->select('supervisor,assistant,lecturer')
                                     ->where(['id'=>$list])
                                     ->all();
        $FinalList = array();

        foreach ($TeacherList as $key => $value) {
            $FinalList[] = $value['supervisor'];
            $FinalList[] = $value['assistant'];
            $FinalList[] = $value['lecturer'];
        }

        //可见创建者id
        $admin_id =  \common\models\CourseMaterial::find()
                                       ->select('admin_id')
                                       ->where(['id'=>$course_material_id])
                                       ->asArray()
                                       ->one();
       
       array_unshift($FinalList, (int)$admin_id['admin_id']);

       return array_unique(array_filter($FinalList));
    }

    public function getClasses()
    {
        return $this->hasOne(Classes::className(), ['id' => 'class_id'])->alias('classes');
    }

}

?>