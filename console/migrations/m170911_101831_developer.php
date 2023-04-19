<?php

use yii\db\Migration;

class m170911_101831_developer extends Migration
{
    const TBL_NAME = '{{%developer}}';

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
            'name' => $this->string(32)->notNull()->comment('姓名'),
            'is_main' =>  $this->smallInteger()->notNull()->defaultValue(0)->comment('是否为主要数据'),

            'auth_key' => $this->string(32)->notNull()->comment('认证密钥'),
            'password_hash' => $this->string(100)->notNull()->comment('密码'),
            'password_reset_token' => $this->string(100)->unique()->comment('密码重置Token'),

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