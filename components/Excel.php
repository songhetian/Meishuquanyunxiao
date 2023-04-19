<?php 
	
namespace components;

use yii;
use PHPExcel;
use common\models\Campus;
use common\models\Category;
use common\models\Classes;
use common\models\Format;
use common\models\Query;
use common\models\ActivationCode;

class Excel {

	//定义分页数量(-1时为不分页获取所有数据)
	public static $PageSize = -1;

	//数据标题参数
	public static $TitleData = [];

	//过滤后的数组
	public static $Titles = [];

	/*
	 * 设置excel标题字段
	 * @parames(array,array) 
	 * $title model字段attributelabels数组
	 * $filter 去除不需要字段
	 * date:2017-06-05
	 */
	public static function setTitleData($titles, $filter) 
	{
		foreach ($titles as $key => $value) {
			if(in_array($key, $filter)) {
				unset($titles[$key]);
			}else{
				self::$Titles[] = $key;
				self::$TitleData[] = $value;
			}
		}
	}

	/*
	 * exel创建
	 * @parames(array)
	 * $data 表单数据
	*/
	public static function CreateExcel($data = [], $table, $type = 1) 
	{
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        $excel = new PHPExcel();
        $filename = strtolower(Format::getModelName($table));
        foreach (self::$TitleData as $key => $value) {
        	if($key > 25){
        		$str = chr(65).chr(65 + $key - 26);
        	}else{
        		$str = chr(65 + $key);
        	}
            $excel->getActiveSheet()->setCellValue($str.'1', $value);
        }
        foreach ($data as $key => $value) {
        	foreach (self::$Titles as  $k => $v) {
        		if($k > 25){
	        		$str = (string)chr(65).chr(65 + $k - 26).($key + 2);
	        		$excel->getActiveSheet()->getColumnDimension((string)chr(65 + $k - 26))->setWidth(20);
	        	}else{
	        		$str = (string)chr(65 + $k).($key + 2);
	        		$excel->getActiveSheet()->getColumnDimension((string)chr(65 + $k))->setWidth(20);
	        	}
        		if(isset($value->$v) || $v == 'role'){
        			switch ($v) {
        				case 'name':
        					$val = ($value->name) ? $value->name : '无名称';
	        				break;
	        			case 'campus_id':
	        				$val = ($filename == 'admin') ? Query::concatValue(Campus::className(), $value->campus_id) : $value->campuses->name;
	        				break;
	        			case 'class_id':
	        				$val = ($filename == 'admin') ? Query::concatValue(Classes::className(), $value->class_id) : $value->classes->name;
	        				break;
	        			case 'gender':
	        				$val = $table::getValues('gender', $value->gender);
	        				break;
	        			case 'relationship':
	        				$val = $table::getValues('relationship', $value->relationship);
	        				break;
	        			case 'race':
	        				$val = $value->races->name;
	        				break;
	        			case 'student_type':
	        				$val = $table::getValues('student_type', $value->student_type);
	        				break;
	        			case 'career_pursuit_type':
	        				$val = $table::getValues('career_pursuit_type', $value->career_pursuit_type);
	        				break;
	        			case 'residence_type':
	        				$val = $table::getValues('residence_type', $value->residence_type);
	        				break;
	        			case 'grade':
	        				$val = $table::getValues('grade', $value->grade);
	        				break;
	        			case 'province':
	        				$val = $value->provinces->name;
	        				break;
	        			case 'city':
	        				$val = $value->citys->name;
	        				break;
	        			case 'united_exam_province':
	        				$val = $value->unitedExamProvinces->name;
	        				break;
	        			case 'is_graduation':
	        				$val = $table::getValues('is_graduation', $value->is_graduation);
	        				break;
	        			case 'is_all_visible':
	        				$val = $table::getValues('is_all_visible', $value->is_all_visible);
	        				break;
	        			case 'created_at':
	        				$val = date('Y/m/d H:i:s', $value->created_at);
	        				break;
	        			case 'updated_at':
	        				$val = date('Y/m/d H:i:s', $value->updated_at);
	        				break;
	        			case 'is_review':
	        				$val = $table::getValues('is_review', $value->is_review);
	        				break;
	        			case 'role':
	        				$role = Yii::$app->authManager->getRolesByUser($value->id);
                            $val = $role[key($role)]->description;
	        				break;
	        			case 'category_id':
	        				$val = Query::concatValue(Category::className(), $value->category_id);
	        				break;
	        			case 'class_period_id':
	        				$val = $value->classPeriods->name;
	        				break;
	        			case 'instructor':
	        				$val = $value->instructors->name;
	        				break;
	        			case 'instruction_method_id':
	        				$val = $value->instructionMethods->name;
	        				break;
	        			case 'course_material_id':
	        				$val = $value->courseMaterials->name;
	        				break;
	        			case 'started_at':
	        				$val = date('Y/m/d H:i:s', $value->started_at);
	        				break;
	        			case 'ended_at':
	        				$val = date('Y/m/d H:i:s', $value->ended_at);
	        				break;
	        			case 'admin_id':
	        				$val = $value->admins->name;
	        				break;
	        			default:
	        				$val = $value->$v;
	        				break;
	        		}
        		}else{
        			if($v == 'code_name'){
	        			$val  = ActivationCode::findOne(['relation_id' => $value->id, 'type' => $type])->code;
        			}elseif ($v == 'code_time') {
        				$val  = ActivationCode::GetUserEndTime($value->id, $type) > 0 ? ActivationCode::GetUserEndTime($value->id, $type).'天' : "已过期";
        			}else{
        				$val = Yii::t('backend', 'Is Not Set');
        			}
        		}
        		//创建数据行
        		if($val){
        			$excel->getActiveSheet()->setCellValue($str, $val);
        		}
        	}
        }
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');
        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save('php://output');
        exit;
	}
}
?>