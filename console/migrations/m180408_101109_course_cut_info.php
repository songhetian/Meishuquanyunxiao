<?php

use yii\db\Migration;

class m180408_101109_course_cut_info extends Migration
{
    const TBL_NAME = '{{%course_cut_info}}';

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
            'course_id' => $this->integer()->notNull()->comment('课程'),
            'class_period_id' => $this->integer()->notNull()->comment('上课时间'),
            'time' => $this->string(32)->notNull()->comment('日期'),

            'status' => $this->smallInteger()->notNull()->defaultValue(0)->comment('状态'),
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
