<?php

use yii\db\Migration;

class m180426_065349_leave_audit extends Migration
{
    const TBL_NAME = '{{%leave_audit}}';
    
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
            'leave_id' => $this->integer()->notNull()->comment('请假ID'),
            'user_role' => $this->string(32)->notNull()->comment('身份'),
            'audit_id' => $this->integer()->notNull()->comment('审核人'),
            'position' => $this->integer()->notNull()->comment('顺序'),
            'processing_state' => $this->smallInteger()->notNull()->defaultValue(0)->comment('审核状态'),
            'processing_at' => $this->integer()->comment('审核时间'),

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
