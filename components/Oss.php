<?php

namespace components;

use chonder\AliyunOSS\AliyunOSS;
use Yii;
use yii\web\UploadedFile;
use common\models\Curl;

class OSS {

    public $ossClient;

    public function __construct($isInternal = false)
    {
        $serverAddress = $isInternal ? Yii::$app->params['oss']['ossServerInternal'] : Yii::$app->params['oss']['ossServer'];
        $this->ossClient = AliyunOSS::boot(
            $serverAddress,
            Yii::$app->params['oss']['AccessKeyId'],
            Yii::$app->params['oss']['AccessKeySecret']
        );
    }

    public static function upload($model, $studio, $table, $field)
    {
        $instance = UploadedFile::getInstance($model, $field);

        if ($instance) {
            //$oss = new OSS(true); // 上传文件使用内网，免流量费
            $oss = new OSS();

            $file_name = $oss->uuid($instance->extension);

            $ossKey = $studio.'/'.$table.'/'.$field.'/'.$file_name;
            $filePath = $instance->tempName;

            $oss->ossClient->setBucket(Yii::$app->params['oss']['Bucket']);
            $oss->ossClient->uploadFile($ossKey, $filePath);

            return $file_name;
        }
        return $model->$field; 
    }

    public static function ossUpload($filePath, $studio, $table, $field, $fileName)
    {
        $oss = new OSS();
        $ossKey = $studio.'/'.$table.'/'.$field.'/'.$fileName;
        $oss->ossClient->setBucket(Yii::$app->params['oss']['Bucket']);
        $oss->ossClient->uploadFile($ossKey, $filePath);
        @unlink($filePath);
        return true;
    }

    public static function uploads($model, $studio, $table, $field)
    {
        $instances = UploadedFile::getInstances($model, $field);
        $res = [];
        if ($instances) {
            foreach ($instances as $instance) {
                //$oss = new OSS(true); // 上传文件使用内网，免流量费
                $oss = new OSS();
                
                $file_name = $oss->uuid($instance->extension);
                $ossKey = $studio.'/'.$table.'/'.$field.'/'.$file_name;
                $filePath = $instance->tempName;

                $oss->ossClient->setBucket(Yii::$app->params['oss']['Bucket']);
                $oss->ossClient->uploadFile($ossKey, $filePath);
                $res[] = $file_name;
            }
        }
        return $res; 
    }

    /**
     * 生成uuid图片名称
     */
    public function uuid($ext)
    {
        $chars = md5(uniqid(mt_rand(), true));  
        $uuid  = substr($chars, 0, 8) . '-';  
        $uuid .= substr($chars, 8, 4) . '-';  
        $uuid .= substr($chars, 12, 4) . '-';  
        $uuid .= substr($chars, 16, 4) . '-';  
        $uuid .= substr($chars, 20, 12);

        return md5($uuid).'.'.$ext;
    }

    public static function getUrl($studio, $table, $field, $ossKey)
    {
        $oss = new OSS();
        $oss->ossClient->setBucket(Yii::$app->params['oss']['Bucket']);
        return preg_replace('/(.*)\?OSSAccessKeyId=.*/', '$1', $oss->ossClient->getUrl($studio.'/'.$table.'/'.$field.'/'.$ossKey, new \DateTime("+1 day")));
    }

    public static function getUrl1($studio, $ossKey)
    {
        $oss = new OSS();
        $oss->ossClient->setBucket(Yii::$app->params['oss']['Bucket']);
        return preg_replace('/(.*)\?OSSAccessKeyId=.*/', '$1', $oss->ossClient->getUrl($studio.'/'.$ossKey, new \DateTime("+1 day")));
    }

    public static function getClassImg($name)
    {
        $oss = new OSS();
        $oss->ossClient->setBucket(Yii::$app->params['oss']['Bucket']);
        return preg_replace('/(.*)\?OSSAccessKeyId=.*/', '$1', $oss->ossClient->getUrl('classify/'.$name.'.jpeg', new \DateTime("+1 day")));
    }

    public static function getIcon($name)
    {
        $oss = new OSS();
        $oss->ossClient->setBucket(Yii::$app->params['oss']['Bucket']);
        return preg_replace('/(.*)\?OSSAccessKeyId=.*/', '$1', $oss->ossClient->getUrl('icon/'.$name, new \DateTime("+1 day")));
    }

    public static function delFile($table, $field, $ossKey)
    {
        $oss = new OSS();
        $oss->ossClient->setBucket(Yii::$app->params['oss']['Bucket']);
        $oss->ossClient->delFile($table.'/'.$field.'/'.$ossKey);
    }

    public static function Exist($studio, $fileName='teacher.jpg')
    {
        $oss = new OSS();
        $oss->ossClient->setBucket(Yii::$app->params['oss']['Bucket']);
        $oss->ossClient->getObject($studio.'/'.$fileName);
    }

    public static function createBucket($bucketName)
    {
        $oss = new OSS();
        return $oss->ossClient->createBucket($bucketName);
    }

    public static function getAllObjectKey($bucketName)
    {
        $oss = new OSS();
        return $oss->ossClient->getAllObjectKey($bucketName);
    }

}