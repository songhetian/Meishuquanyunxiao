<?php

use yii\db\Migration;

class m170602_092641_studio extends Migration
{
    const TBL_NAME = '{{%studio}}';
    
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
            'name' => $this->string(32)->notNull()->unique()->comment('画室名称'),
            'teacher_num' => $this->integer()->notNull()->defaultValue(50)->comment('默认老师数量'),
            'review_num' => $this->integer()->notNull()->comment('购买数量'),
            'one_year_num' => $this->integer()->notNull()->comment('一年期激活码数量'),
            'two_years_num' => $this->integer()->notNull()->comment('两年期激活码数量'),
            'three_years_num' => $this->integer()->notNull()->comment('三年期激活码数量'),
            'jpush_app_key' => $this->string(32)->notNull()->comment('极光 AppKey'),
            'jpush_master_secret' => $this->string(32)->notNull()->comment('极光 MasterSecret'),
            'is_view' => $this->integer()->notNull()->comment('是否看美术圈资源'),
            'is_press' => $this->integer()->notNull()->comment('是否为出版社'),
            'image' => $this->string(200)->comment('背景图'),
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