<?php
namespace teacher\modules\v2\models;

use Yii;
use components\Oss;
use common\models\Format;

class EbookCollect extends \common\models\EbookCollect
{
	public static $admin;

	//身份
	public static $role_type;


    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
            	$flag = self::findOne([
								'admin_id' => $this->admin_id,
								'role'     => $this->role,
								'status'   => 10,
								'ebook_id' => $this->ebook_id
            			]);
            	if($flag) {
            		$this->addError('ebook_id', '该电子书已经收藏');
            		return false;
            	}
            }
            return true;
        }
        return false;
    }

    /**
     * [getCancel 获取已经取消的]
     * @开发者    tianhesong
     * @创建时间   2020-05-29T13:31:51+0800
     * @return [type]                   [description]
     */
    public static function getCancel($ebook_id,$admin_id,$role) {
    	$res = self::findOne([
	    		     'ebook_id' => $ebook_id,
	    		     'admin_id' => $admin_id,
	    		     'role'     => $role,
	    		     'status'   => 0
    			]);

    	return $res;
    }

    public static function getListById($ebook_id,$admin_id,$role) {
    	$res = self::findOne([
	    		     'ebook_id' => $ebook_id,
	    		     'admin_id' => $admin_id,
	    		     'role'     => $role,
	    		     'status'   => 10
    			]);

    	return $res;
    }
    /**
     * [getCollects 获取全部收藏]
     * @开发者    tianhesong
     * @创建时间   2020-06-03T15:57:14+0800
     * @param  [type]                   $admin_id [description]
     * @param  [type]                   $role     [description]
     * @return [type]                             [description]
     */
    public static function getCollects($admin_id,$role) {
    	$lists = self::find()
		    		  ->select('ebook_id')
		    		  ->where([
							'admin_id' =>$admin_id,
							'role'     => $role,
							'status'   =>10
		    		  ])
		    		  ->asArray()
		    		  ->all();

		return array_column($lists, 'ebook_id');
    } 

    public function fields()
	{
	    $fields = parent::fields();


	    $fields['id']  = function () {
	    	return $this->ebook_id;
	    };

	    $fields['pic_url_v2'] = function () {

	    	return $this->pic_url.'?x-oss-process=style/thumb';
	    };
	    $fields['size'] = function () {
	        $sizes = Yii::$app->params['oss']['Size'];
	        foreach ($sizes as $k => $v) {
	            $size['image_' . $k] = $v;
	        }
	        return $size;
	    };

	    $fields['title'] = function () {
	    	return $this->name;
	    };
	    $fields['category'] = function () {

	    	return $this->category_id;
	    };
	    $fields['is_collect'] = function () {
	    	$list = self::getCollects(self::$admin,self::$role_type);
	    	return (in_array($this->ebook_id, $list)) ? true :false;
	    };
	    $fields['is_public'] = function () {
	    	return ($this->is_public == 10) ? true :false;
	    };

	    unset(
	    	$fields['is_top'],
	    	$fields['name'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['role'],
            $fields['publishing_company'],
            $fields['studio_id'],
            $fields['category_id'],
            $fields['status']
        );
	    return $fields;
	}
}
