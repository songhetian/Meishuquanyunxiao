<?php

use yii\db\Migration;

class m170225_123529_message extends Migration
{
    const TBL_NAME = '{{%message}}';

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
            'message_category_id' => $this->smallInteger()->notNull()->comment('消息类型'),
            'campus_id' => $this->string(100)->notNull()->comment('校区'),
            'category_id' => $this->string(100)->comment('科目'),
            'class_id' => $this->string(100)->comment('班级'),
            'user_id' => $this->smallInteger()->comment('用户'),
            'title' => $this->string(32)->notNull()->comment('标题'),
            'content' => $this->text()->comment('内容'),
            'correlated_id' => $this->smallInteger()->comment('关联ID'),
            'code' => $this->smallInteger()->comment('编码'),
            'admin_id' => $this->integer()->notNull()->comment('发布者'),
            
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