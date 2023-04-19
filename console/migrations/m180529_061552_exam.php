<?php

use yii\db\Migration;

class m180529_061552_exam extends Migration
{
    const TBL_NAME = '{{%exam}}';
    
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
            'title' => $this->string(32)->notNull()->comment('题目'),
            'type' => $this->integer()->notNull()->comment('类型'),
            'time' => $this->integer()->notNull()->comment('考试时间'),
            'category_id' =>  $this->integer()->notNull()->comment('考试科目'),
            'class_id' => $this->string(100)->notNull()->comment('参与班级'),
            'content' => $this->text()->notNull()->comment('内容'),
            'image' => $this->string(200)->comment('图片'),
            'length' => $this->integer()->notNull()->comment('考试时长'),
            'require' => $this->text()->comment('要求'),
            'release_state' => $this->smallInteger()->notNull()->defaultValue(0)->comment('发布状态'),
            'admin_id' => $this->integer()->notNull()->comment('创建人'),

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
