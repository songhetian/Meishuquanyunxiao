<?php

use yii\db\Migration;

class m180425_070310_leave extends Migration
{
    const TBL_NAME = '{{%leave}}';
    
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
            'user_role' => $this->string(32)->notNull()->comment('身份'),
            'type' => $this->integer()->notNull()->comment('类型'),
            'started_at' => $this->integer()->notNull()->comment('开始时间'),
            'ended_at' => $this->integer()->notNull()->comment('结束时间'),
            'day' => $this->float()->notNull()->comment('天数'),
            'reason' => $this->text()->notNull()->comment('事由'),
            'image' => $this->string(200)->comment('图片'),
            'account_id' => $this->integer()->notNull()->comment('请假人'),
            'is_urged' => $this->smallInteger()->notNull()->defaultValue(0)->comment('是否促办'),

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
