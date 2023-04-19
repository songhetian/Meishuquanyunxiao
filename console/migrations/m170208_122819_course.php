<?php

use yii\db\Migration;

class m170208_122819_course extends Migration
{
    const TBL_NAME = '{{%course}}';

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
            'class_period_id' => $this->smallInteger()->notNull()->comment('上课时间'),
            'class_id' => $this->smallInteger()->notNull()->comment('所属班级'),
            'category_id' =>  $this->smallInteger()->notNull()->comment('科目'),
            'instructor' => $this->smallInteger()->notNull()->comment('教学老师'),
            'instruction_method_id' => $this->smallInteger()->notNull()->comment('教学形式'),
            'course_material_id' => $this->smallInteger()->comment('关联教案'),
            'started_at' => $this->integer()->notNull()->comment('开始时间'),
            'ended_at' => $this->integer()->notNull()->comment('结束时间'),
            'class_content' => $this->text()->comment('教学内容'),
            'class_emphasis' => $this->text()->comment('教学重点'),
            'note' => $this->text()->comment('备注'),
            'admin_id' => $this->integer()->notNull()->comment('上传者'),
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