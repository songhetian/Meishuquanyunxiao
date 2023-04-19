<?php

use yii\db\Migration;

class m170225_035132_user_homework extends Migration
{
    const TBL_NAME = '{{%user_homework}}';
    
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
            'user_id' => $this->integer()->notNull()->comment('学生'),
            'course_material_id' =>  $this->smallInteger()->notNull()->comment('所属教案'),
            'image' => $this->string(200)->comment('作品'),
            'evaluator' => $this->integer()->comment('点评讲师'),
            'comments' => $this->text()->comment('评语'),
            'score' =>$this->float()->comment('得分'),

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