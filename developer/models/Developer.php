<?php

namespace developer\models;

use Yii;
use yii\web\IdentityInterface;
use backend\models\ActiveRecord;

/**
 * This is the model class for table "{{%developer}}".
 *
 * @property integer $id
 * @property string $phone_number
 * @property string $name
 * @property integer $is_main
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Developer extends ActiveRecord implements IdentityInterface
{
    public $username;

    const AUTH_KEY = '134679';

    const MAIN_NOT_YET = 0;
    const MAIN_ED = 10;

    public static function tableName()
    {
        return '{{%developer}}';
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->setPassword($this->password_hash);
                $this->generateAuthKey();
            }else{
                if($this->password_hash != $this->getOldAttribute('password_hash')){
                    $this->setPassword($this->password_hash);
                }
            }
            return true;
        }
        return false;
    }

    public function rules()
    {
        return [
            //特殊需求
            [['phone_number', 'password_hash'], 'required'],
            [['name'], 'required', 'on' => ['create','update']],
            [['phone_number', 'password_reset_token'], 'unique', 'on' => ['create','update']],
            //字段规范
            ['phone_number', 'match', 'pattern' => '/^(0|86|17951)?(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57])[0-9]{8}$/', 'message' => '请填写有效手机号。'],
            ['password_hash', 'string', 'min' => 6],
            ['auth_key', 'default', 'value' => self::AUTH_KEY], 
            ['is_main', 'default', 'value' => self::MAIN_NOT_YET],
            ['status', 'default', 'value' => self::STATUS_ACTIVE], 
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
            //字段类型
            [['is_main', 'created_at', 'updated_at', 'status'], 'integer'],
            ['phone_number', 'string', 'max' => 11],
            [['name', 'auth_key'], 'string', 'max' => 32],
            [['password_hash', 'password_reset_token'], 'string', 'max' => 100],
            [['username'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'phone_number' => Yii::t('app', '手机号'),
            'name' => Yii::t('app', '姓名'),
            'is_main' => Yii::t('app', '是否为主要数据'),
            'auth_key' => Yii::t('app', '认证密钥'),
            'password_hash' => Yii::t('app', '密码'),
            'password_reset_token' => Yii::t('app', '密码重置Token'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    public function consoleSignup()
    {
        if ($this->validate()) {
            $this->is_main = self::MAIN_ED;
            if($this->save()){
                return $this;
            }
        }
        return false;
    }

    /**
     * 根据ID查询用户
     *
     * @param string|integer $id 被查询的ID
     * @return IdentityInterface|null 通过ID匹配到的用户对象
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * 根据 token 查询用户 RESTFUL认证使用
     *
     * @param string $token 被查询的 token
     * @return IdentityInterface|null 通过 token 得到的用户对象
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string 当前用户ID
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @return string 当前用户的（cookie）认证密钥
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * 验证登录密钥
     * 
     * @param string $authKey
     * @return boolean 如果当前用户身份验证的密钥是有效的
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * 根据手机号查询用户
     *
     * @param string $phone_number
     * @return static|null
     */
    public static function findByPhoneNumber($phone_number)
    {
        return static::findOne(['phone_number' => $phone_number, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * 密码验证
     *
     * @param string $password
     * @return boolean
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * 生成并设置密码
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * 生成 记住我 的认证密钥
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
}