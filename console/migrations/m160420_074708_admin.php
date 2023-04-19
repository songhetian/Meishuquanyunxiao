<?php

use yii\db\Migration;

class m160420_074708_admin extends Migration
{
    const TBL_NAME = '{{%admin}}';

    /**
     * 创建表选项
     * @var string
     */
    public $tableOptions = null;

    /**
     * 是否创建为事务表
     * @var bool
     */
    public $useTransaction = true;
    
    public function safeUp()
    {
        if ($this->db->driverName === 'mysql') {
            $engine = $this->useTransaction ? 'InnoDB' : 'MyISAM';
            $this->tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE=' . $engine;
        }

        $this->execute($this->delTable());
        $this->createTable(self::TBL_NAME, [
            'id' => $this->primaryKey()->unsigned()->comment('ID'),
            'phone_number' => $this->string(11)->notNull()->unique()->comment('手机号'),
            'name' => $this->string(32)->comment('姓名'),
            'image' => $this->string(100)->comment('用户头像'),
            'campus_id' => $this->string(100)->notNull()->comment('可见校区'),
            'category_id' => $this->string(100)->comment('可见科目'),
            'expert_category' => $this->string(30)->comment('擅长科目'),
            'class_id' => $this->string(100)->comment('可见班级'),
            'province' => $this->smallInteger()->comment('所在地区'),
            'gender' => $this->smallInteger()->comment('性别'),
            'is_all_visible' => $this->smallInteger()->notNull()->defaultValue(10)->comment('是否全部可见'),
            'is_main' =>  $this->smallInteger()->notNull()->defaultValue(0)->comment('是否为主要数据'),
            'usersig' => $this->text()->comment('腾讯云凭证'),
            
            'auth_key' => $this->string(32)->notNull()->comment('认证密钥'),
            'password_hash' => $this->string(100)->notNull()->comment('密码'),
            'password_reset_token' => $this->string(100)->unique()->comment('密码重置Token'),
            'token_value' => $this->string(32)->comment('Token值'),
            
            'created_at' => $this->integer()->notNull()->comment('创建时间'),
            'updated_at' => $this->integer()->notNull()->comment('更新时间'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('状态'),
        ], $this->tableOptions);
    }

    private function delTable()
    {
        return "DROP TABLE IF EXISTS ".self::TBL_NAME.";";
    }

    public function safeDown()
    {
        $this->dropTable(self::TBL_NAME);
    }
}