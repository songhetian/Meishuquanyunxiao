<?php
namespace teacher\modules\v2\controllers;

use Yii;
use common\models\Studio;
use teacher\modules\v2\models\SendMessage;
use teacher\modules\v2\models\ActivationCode;

class ForFriendController extends MainController
{
    public $modelClass = 'teacher\modules\v2\models\NewStudio';
    
    /**
     * [actionIndex 获取画室基本信息]
     * @copyright 
     * @version   
     * @date      
     * @param     string       $name [画室名]
     * 
     */
    public function actionList()
    {
        $modelClass = $this->modelClass;

        $name = Yii::$app->request->post('name');

        $list = $modelClass::find()
                ->where(['status' => $modelClass::STATUS_ACTIVE])
                ->andFilterWhere(['like','name',$name])
                ->all();

        $NewList  = array();

        foreach ($list as $key => $value) {

            $new = $value->toArray();

            $NewList[] = array(
                         'id'           => $value->id,
                         'name'         => $value->name,
                         'teacherList'  => array(
                                    array(
                                           'id' => 1,
                                           'title'    => '老师总数',
                                           'number'   => $value->teacher_num,
                                           'param'    => 'teacher_add_num',
                                           'otherArray' =>  array(
                                                                    array(
                                                                        'id'     => 1,
                                                                        'title'  => '已生成',
                                                                        'number' =>  $new['teacher_sc']
                                                                    ),
                                                                    array(
                                                                        'id'     => 2,
                                                                        'title'  => '未使用',
                                                                        'number' =>  $new['not_teacher_num']
                                                                    ),
                                                                    array(
                                                                        'id'     => 3,
                                                                        'title'  => '已激活',
                                                                        'number' =>  $new['teacher_jh']
                                                                    ),
                                                                    array(
                                                                        'id'     => 4,
                                                                        'title'  => '未激活',
                                                                        'number' =>  $new['teacher_wjh']
                                                                    )                                                         
                                           ),
                                ),
                        ),
                         'studentList'  => array(
                                            array(
                                               'id' => 2,
                                               'title'    => '1年总数',
                                               'number'   => $value->one_year_num,
                                               'param'    => 'one',
                                               'otherArray' =>  array(
                                                                    array(
                                                                        'id'     => 1,
                                                                        'title'  => '已生成',
                                                                        'number' =>  $new['one_sc']
                                                                    ),
                                                                    array(
                                                                        'id'     => 2,
                                                                        'title'  => '未使用',
                                                                        'number' =>  $new['not_one_num']
                                                                    ),
                                                                    array(
                                                                        'id'     => 3,
                                                                        'title'  => '已激活',
                                                                        'number' =>  $new['one_jh']
                                                                    ),
                                                                    array(
                                                                        'id'     => 4,
                                                                        'title'  => '未激活',
                                                                        'number' =>  $new['one_wjh']
                                                                    )                                                          
                                               ),
                                            ),
                                            array(
                                               'id' => 3,
                                               'title'    => '2年总数',
                                               'number'   => $value->two_years_num,
                                               'param'    => 'two',
                                               'otherArray' =>  array(
                                                                    array(
                                                                        'id'     => 1,
                                                                        'title'  => '已生成',
                                                                        'number' =>  $new['two_sc']
                                                                    ),
                                                                    array(
                                                                        'id'     => 2,
                                                                        'title'  => '未使用',
                                                                        'number' =>  $new['not_two_num']
                                                                    ),
                                                                    array(
                                                                        'id'     => 3,
                                                                        'title'  => '已激活',
                                                                        'number' =>  $new['two_jh']
                                                                    ),
                                                                    array(
                                                                        'id'     => 4,
                                                                        'title'  => '未激活',
                                                                        'number' =>  $new['two_wjh']
                                                                    )                                                          
                                               ),
                                            ),
                                            array(
                                               'id' => 4,
                                               'title'    => '3年总数',
                                               'number'   => $value->three_years_num,
                                               'param'    => 'three',
                                               'otherArray' =>  array(
                                                                    array(
                                                                        'id'     => 1,
                                                                        'title'  => '已生成',
                                                                        'number' =>  $new['three_sc']
                                                                    ),
                                                                    array(
                                                                        'id'     => 2,
                                                                        'title'  => '未使用',
                                                                        'number' =>  $new['not_three_num']
                                                                    ),
                                                                    array(
                                                                        'id'     => 3,
                                                                        'title'  => '已激活',
                                                                        'number' =>  $new['three_jh']
                                                                    ),
                                                                    array(
                                                                        'id'     => 4,
                                                                        'title'  => '未激活',
                                                                        'number' =>  $new['three_wjh']
                                                                    )                                                          
                                               ),
                                            ),
                                            array(
                                               'id' => 5,
                                               'title'    => '2021试用总数',
                                               'number'   => $value->three_month_num,
                                               'param'    => 'three_yue',
                                               'otherArray' =>  array(
                                                                    array(
                                                                        'id'     => 1,
                                                                        'title'  => '已生成',
                                                                        'number' =>  $new['three_yue_sc']
                                                                    ),
                                                                    array(
                                                                        'id'     => 2,
                                                                        'title'  => '未使用',
                                                                        'number' =>  $new['not_three_yue_num']
                                                                    ),
                                                                    array(
                                                                        'id'     => 3,
                                                                        'title'  => '已激活',
                                                                        'number' =>  $new['three_yue_jh']
                                                                    ),
                                                                    array(
                                                                        'id'     => 4,
                                                                        'title'  => '未激活',
                                                                        'number' =>  $new['three_yue_wjh']
                                                                    )                                                          
                                               ),
                                            ),
                                            array(
                                               'id' => 6,
                                               'title'    => '1月总数',
                                               'number'   => $value->one_month_num,
                                               'param'    => 'one_yue',
                                               'otherArray' =>  array(
                                                                    array(
                                                                        'id'     => 1,
                                                                        'title'  => '已生成',
                                                                        'number' =>  $new['one_yue_sc']
                                                                    ),
                                                                    array(
                                                                        'id'     => 2,
                                                                        'title'  => '未使用',
                                                                        'number' =>  $new['not_one_yue_num']
                                                                    ),
                                                                    array(
                                                                        'id'     => 3,
                                                                        'title'  => '已激活',
                                                                        'number' =>  $new['one_yue_jh']
                                                                    ),
                                                                    array(
                                                                        'id'     => 4,
                                                                        'title'  => '未激活',
                                                                        'number' =>  $new['one_yue_wjh']
                                                                    )                                                          
                                               ),
                                            ),
                         ),
            );
        }




        $_GET['message'] = Yii::t('api', 'Sucessfully Get List');
        
        return $NewList;
    }


    //画室增加数量
    public function actionAddNum($id,$type,$number,$text) {

        $modelClass = $this->modelClass;

        if($text != '234249') {
            return SendMessage::sendErrorMsg("没有权限");
        }


        $number  =  $number ? $number : 0 ;

        switch ($type) {
            case 'teacher_add_num':
                $key = 'teacher_num';
                break;
            case 'one':
                $key = 'one_year_num';
                break;
            case 'two':
                 $key = 'two_years_num';
                break;
            case 'three':
                 $key = 'three_years_num';
                 break;
            case 'three_yue':
                $key = 'three_month_num';
                break;
            case 'one_yue':
                $key = 'one_month_num';
                break;
            
            default:
                return SendMessage::sendErrorMsg("参数错误");
                break;
        }

        $Studio = $modelClass::findOne($id);
        
        if($type == 'teacher_add_num') {
            $array = array($key => $number);

        }else{
            $array = array($key => $number,'review_num'=>$number);
        }

        if($Studio::updateAllCounters($array,['id'=>$id])) {

            return SendMessage::sendSuccessMsg("增加成功");
        }else{
            return SendMessage::sendErrorMsg("增加失败");
        }
    }

}