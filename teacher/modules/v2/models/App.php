<?php

namespace teacher\modules\v2\models;

use Yii;


class App extends \common\models\App
{	
    public function fields()
	{
	    $fields = parent::fields();
	    $fields['ios'] = function () {
	        return 'itms-services://?action=download-manifest&amp;url='.$this->plist;
	    };
	    $fields['ios_url'] = function () {
	        return $this->plist;
	    };
	    $fields['android'] = function () {
	        return $this->apk;
	    };
	    unset(
	    	$fields['id'],
	    	$fields['studio_id'],
	    	$fields['ipa'],
	    	$fields['plist'],
	    	$fields['apk'],
	    	$fields['logo'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['status']
        );
	    return $fields;
	}


	public static function GetInco($is_review,$role,$status,$studio_id) {

		if($status) {
			$code        = "已绑定激活码";
			$family_code = "已绑定学生";
		}else{
			$code        = "绑定激活码";
			$family_code = "绑定学生";
		}

		if($studio_id == 183) {

			$yqhy = "邀请好友";
		}else{
			$yqhy = "下载二维码";
		}


		$teacher = array(
			'vip.png'             => 1,
			'yaoqinghaoyou.png'   => 1,
			'wodesucaiku.png'     => 1,
			'xiaoyuansucaiku.png' => 1,
			'tushuguan.png'       => 1,
			'wodekejian.png'      => 1,
			'wodepigai.png'       => 1,
			'bangdingjiguoma.png' => 0,
			'bangdingjihuoma.png' => 0,
			'jigouruzhu.png'      => 1,
			'yijianfankui.png'    => 1,
			'lianxiwomen.png'     => 1
		);

		$student  = array(
			'vip.png'             => 1,
			'yaoqinghaoyou.png'   => 1,
			'wodesucaiku.png'     => 1,
			'xiaoyuansucaiku.png' => 1,
			'wodekejian.png'  => 1,
			'wodezuoye.png'       => 1,
			'bangdingjiguoma.png' => 0,
			'bangdingjihuoma.png' => 0,
			'yijianfankui.png'    => 1,
			'lianxiwomen.png'     => 1
			
		);

		$family  = array(
			'vip.png'             => 1,
			'yaoqinghaoyou.png'      => 1,
			'wodesucaiku.png'        => 1,
			'bangdingjiguoma.png'    => 0,
			'bangdingxuesheng.png'   => 0,
			'wodezuoye.png'          => 1,
			'yijianfankui.png'       => 1,
			'lianxiwomen.png'        => 1
		); 


		$teacher_icon_name  = array(
			'vip.png'             => '会员中心',
			'yaoqinghaoyou.png'   => $yqhy,
			'wodesucaiku.png'     => '我的素材库',
			'xiaoyuansucaiku.png' => '校园素材库',
			'tushuguan.png'       => '校园图书馆',
			'wodekejian.png'      => '我的课件',
			'wodepigai.png'       => '我的批改',
			'bangdingjiguoma.png' => '绑定机构码',
			'bangdingjihuoma.png' => $code,
			'jigouruzhu.png'      => '机构入驻',
			'yijianfankui.png'    => '意见反馈',
			'lianxiwomen.png'     => '联系我们'
		);
		$student_icon_name  = array(
			'vip.png'             => '会员中心',
			'yaoqinghaoyou.png'   => $yqhy,
			'wodesucaiku.png'     => '我的素材库',
			'xiaoyuansucaiku.png' => '校园素材库',
			'wodekejian.png'  => '课件收藏',
			'wodezuoye.png'       => '我的作业',
			'bangdingjiguoma.png' => '绑定机构码',
			'bangdingjihuoma.png' => $code,
			'yijianfankui.png'    => '意见反馈',
			'lianxiwomen.png'     => '联系我们'
		);
		$family_icon_name  = array(
			'vip.png'             => '会员中心',
			'yaoqinghaoyou.png'      => $yqhy,
			'wodesucaiku.png'        => '我的素材库',
			'bangdingjiguoma.png'    => '绑定机构码',
			'bangdingxuesheng.png'   => $family_code,
			'wodezuoye.png'          => '孩子作业',
			'yijianfankui.png'       => '意见反馈',
			'lianxiwomen.png'        => '联系我们'
		);

		$teacher_url  = array(
			'vip.png'             => 'YSVipViewController',
			'yaoqinghaoyou.png'   => $yqhy,
			'wodesucaiku.png'     => 'YSMyLibraryController',
			'xiaoyuansucaiku.png' => 'YSCompusLibraryController',
			'tushuguan.png'       => 'YSSchoolLibraryController',
			'wodekejian.png'      => 'YSMyCourseViewController',
			'wodepigai.png'       => 'YSHomeworkController',
			'bangdingjiguoma.png' => '绑定机构码',
			'bangdingjihuoma.png' => '绑定激活码',
			'jigouruzhu.png'      => 'YSStudioInController',
			'yijianfankui.png'    => 'JGUserFeedBackViewController',
			'lianxiwomen.png'     => 'YSConnectionController'
		);

		$student_url  = array(
			'vip.png'             => 'YSVipViewController',
			'yaoqinghaoyou.png'   => $yqhy,
			'wodesucaiku.png'     => 'YSMyLibraryController',
			'xiaoyuansucaiku.png' => 'YSCompusLibraryController',
			'wodekejian.png'      => 'YSMyCourseViewController',
			'wodezuoye.png'       => 'YSHomeworkController',
			'bangdingjiguoma.png' => '绑定机构码',
			'bangdingjihuoma.png' => '绑定激活码',
			'yijianfankui.png'    => 'JGUserFeedBackViewController',
			'lianxiwomen.png'     => 'YSConnectionController'
		);

		$family_url  =  array(
			'vip.png'                => 'YSVipViewController',
			'yaoqinghaoyou.png'      => $yqhy,
			'wodesucaiku.png'        => 'YSMyLibraryController',
			'bangdingjiguoma.png'    => '绑定机构码',
			'bangdingxuesheng.png'   => '绑定学生',
			'wodezuoye.png'          => 'YSHomeworkController',
			'yijianfankui.png'       => 'JGUserFeedBackViewController',
			'lianxiwomen.png'        => 'YSConnectionController'

		);

		if($studio_id != 183) {

			$is_review = 0;

			unset(
				$teacher['vip.png'],
				
				$teacher['bangdingjiguoma.png'],
				$teacher['lianxiwomen.png'],
				$teacher['jigouruzhu.png'],
				$student['vip.png'],
				
				$student['bangdingjiguoma.png'],
				$student['lianxiwomen.png'],
				$family['vip.png'],
				
				$family['bangdingjiguoma.png'],
				$family['lianxiwomen.png'],
				$teacher_icon_name['vip.png'],
				
				$teacher_icon_name['bangdingjiguoma.png'],
				$teacher_icon_name['lianxiwomen.png'],
				$teacher_icon_name['jigouruzhu.png'],
				$student_icon_name['vip.png'],
				
				$student_icon_name['bangdingjiguoma.png'],
				$student_icon_name['lianxiwomen.png'],
				$family_icon_name['vip.png'],
			
				$family_icon_name['bangdingjiguoma.png'],
				$family_icon_name['lianxiwomen.png'],
				$teacher_url['vip.png'],
				
				$teacher_url['bangdingjiguoma.png'],
				$teacher_url['lianxiwomen.png'],
				$teacher_url['jigouruzhu.png'],
				$student_url['vip.png'],
			
				$student_url['bangdingjiguoma.png'],
				$student_url['lianxiwomen.png'],
				$family_url['vip.png'],
				
				$family_url['bangdingjiguoma.png'],
				$family_url['lianxiwomen.png']
			);
		}else{
			unset(
				$teacher['tushuguan.png'],
				$teacher_icon_name['tushuguan.png'],
				$teacher_url['tushuguan.png']
			);
		}


		$icons = array();

		if($role == 10) {
			foreach ($teacher as $key => $value) {
				if($value >= $is_review) {
					$icons[] = array(
						'image' => Yii::$app->request->hostInfo.'/icon/'.$key,
						'name'  => $teacher_icon_name[$key],
						'url'   => $teacher_url[$key]
					);
				}
			}
		}elseif($role == 20) {
			foreach ($student as $key => $value) {
				if($value >= $is_review) {
					$icons[] = array(
						'image' => Yii::$app->request->hostInfo.'/icon/'.$key,
						'name'  => $student_icon_name[$key],
						'url'   => $student_url[$key]
					);
				}
			}

		}elseif($role == 30) {
			foreach ($family as $key => $value) {
				if($value >= $is_review) {
					$icons[] = array(
						'image' => Yii::$app->request->hostInfo.'/icon/'.$key,
						'name'  => $family_icon_name[$key],
						'url'   => $family_url[$key]
					);
				}
			}
		}

		return $icons;
	}
}
