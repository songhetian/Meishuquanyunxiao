<?php

namespace teacher\modules\v2\models;

use Yii;
use yii\base\Model;
use components\Server\TimRestApi;

class Tencat extends model
{
   public static $sdkid;

   public static function Create($name,$nickname) {

        $api = new TimRestAPI;

        $api->init(1400055041, 'myadmin');

        $api->generate_user_sig('myadmin', '86400', '/home/keys/private_key', '/home/tls_sig_api/bin/signature');
     
        $res = $api->account_import($name,$nickname,'http://api.teacher.meishuquanyunxiao.com');

        return $res['ErrorCode'];
   }

   public static function CreateNumber($name) {
        $api = new TimRestAPI;

        $api->init(1400055041, $name);

        return $api->generate_user_sig($name, '31536000', '/home/keys/private_key', '/home/tls_sig_api/bin/signature');
   }

   //上传头像

   public static function CreateImage($identifier,$image){
      $api = new TimRestAPI;
      $array = array(
                    array('Tag'=>'Tag_Profile_IM_Image','Value'=>$image)
               );
      $api->init(1400055041, 'myadmin');
      $api->generate_user_sig('myadmin', '86400', '/home/keys/private_key', '/home/tls_sig_api/bin/signature');
      return $api->profile_portrait_set2($identifier,$array);
   }

   //改改昵称
   public static function UpdateName($identifier,$name){
      $api = new TimRestAPI;
      $array = array(
                    array('Tag'=>'Tag_Profile_IM_Nick','Value'=>$name)
               );
      $api->init(1400055041, 'myadmin');
      $api->generate_user_sig('myadmin', '86400', '/home/keys/private_key', '/home/tls_sig_api/bin/signature');
      return $api->profile_portrait_set2($identifier,$array);
   }

   //上传头像
   public static function CreateImage1($identifier){
      $api = new TimRestAPI;
      $api->init(1400055041, 'myadmin');
      $api->generate_user_sig('myadmin', '86400', '/home/keys/private_key', '/home/tls_sig_api/bin/signature');
      return $api->profile_portrait_get($identifier);
   }

   //返回腾讯云姓名
   public static function getName($identifier){
       return static::CreateImage1($identifier)['UserProfileItem'][0]['ProfileItem'][0]['Value'];
   }
   //返回腾讯云姓名
   public static function getImage($identifier){
      return static::CreateImage1($identifier)['UserProfileItem'][0]['ProfileItem'][2]['Value'];
   }

   //添加群成员
   public static function AddUsers($group_id,$member_id,$silence = 0) {
      $api = new TimRestAPI;
      $api->init(1400055041, 'myadmin');
      $api->generate_user_sig('myadmin', '86400', '/home/keys/private_key', '/home/tls_sig_api/bin/signature');
      return $api->group_add_group_member($group_id,$member_id,$silence = 0);
   }

   //添加群自定义信息

   public static function SetCommon($group_id,$app_define_list) {
      $api = new TimRestAPI;
      $api->init(1400055041, 'myadmin');
      $api->generate_user_sig('myadmin', '86400', '/home/keys/private_key', '/home/tls_sig_api/bin/signature');
      return $api->group_modify_group_base_info3($group_id,$app_define_list);
    
   }


   //伪推送
   public static function SendMsg($account_list, $text_content) {
    
      $api = new TimRestAPI;
      $api->init(1400055041, 'myadmin');
      $api->generate_user_sig('myadmin', '86400', '/home/keys/private_key', '/home/tls_sig_api/bin/signature');
      return $api->openim_batch_sendmsg($account_list,$text_content);
   }

   //获取群组信息
   public static function getGroupInfo($group_id) {
      $api = new TimRestAPI;
      $api->init(1400055041, 'myadmin');
      $api->generate_user_sig('myadmin', '86400', '/home/keys/private_key', '/home/tls_sig_api/bin/signature');
      return $api->group_get_group_member_info2($group_id);
   }

   public static function getImageList($identifier,$tag_list=array("Tag_Profile_IM_Image")) {
      $api = new TimRestAPI;
      $api->init(1400055041, 'myadmin');
      $api->generate_user_sig('myadmin', '86400', '/home/keys/private_key', '/home/tls_sig_api/bin/signature');
      return $api->profile_portrait_get2($identifier,$tag_list);
   }


   //设置群头像
   public static function setGroupImage($group_id,$info_set) {
      $api = new TimRestAPI;
      $api->init(1400055041, 'myadmin');
      $api->generate_user_sig('myadmin', '86400', '/home/keys/private_key', '/home/tls_sig_api/bin/signature');
      return $api->group_modify_group_base_info4($group_id,$info_set);
   }


}
