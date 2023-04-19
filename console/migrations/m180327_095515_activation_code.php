<?php

use yii\db\Migration;

class m180327_095515_activation_code extends Migration
{
    const TBL_NAME = '{{%activation_code}}';

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
            'code' => $this->string(8)->unique()->comment('激活码'),
            'type' => $this->smallInteger()->comment('类型'),
            'relation_id' => $this->integer()->comment('关联用户ID'),
            'campus_id' => $this->string(100)->comment('所属校区'),
            'class_id' => $this->string(100)->comment('所属班级'),
            'is_active' => $this->smallInteger()->notNull()->defaultValue(20)->comment('是否激活'),
            'activetime' => $this->smallInteger()->comment('年限'),
            'studio_id' => $this->integer()->notNull()->comment('所属画室'),

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
