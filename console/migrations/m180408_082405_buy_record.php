<?php

use yii\db\Migration;

class m180408_082405_buy_record extends Migration
{
    const TBL_NAME = '{{%buy_record}}';

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
            'buy_id' => $this->integer()->comment('购买者ID'),
            'buy_studio' => $this->integer()->comment('购买者所属画室'),
            'role' => $this->smallInteger()->comment('购买者身份'),
            'gather_id' => $this->integer()->comment('云课件ID'),
            'gather_studio' => $this->integer()->comment('云课件所属画室'),
            'admin_id' => $this->integer()->comment('操作人'),
            'price' => $this->decimal('10,2')->defaultValue(0.00)->comment('价格'),

            'active_at' => $this->integer()->notNull()->comment('过期时间'),
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
