<?php

use yii\db\Migration;

class m180408_101807_course_material_info extends Migration
{
    const TBL_NAME = '{{%course_material_info}}';

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
            'course_material_id' => $this->integer()->notNull()->comment('课件ID'),
            'category_id' => $this->integer()->notNull()->comment('分类ID'),
            'class_content' => $this->text()->notNull()->comment('教学内容'),
            'instruction_method_id' => $this->integer()->notNull()->comment('上课方式'),
            'admin_id' => $this->integer()->notNull()->comment('创建者ID'),
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
