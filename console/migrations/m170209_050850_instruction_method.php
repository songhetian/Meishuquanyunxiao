<?php

use yii\db\Migration;

class m170209_050850_instruction_method extends Migration
{
    const TBL_NAME = '{{%instruction_method}}';

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
            'name' => $this->string(20)->notNull()->comment('名称'),
            
            'created_at' => $this->integer()->notNull()->comment('创建时间'),
            'updated_at' => $this->integer()->notNull()->comment('更新时间'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('状态'),
        ], $this->tableOptions);
        $this->execute($this->getiInstructionMethodSql());
    }

    private function delTable()
    {
        return "DROP TABLE IF EXISTS ".self::TBL_NAME.";";
    }

    public function safeDown()
    {
        $this->dropTable(self::TBL_NAME);
    }

    private function getiInstructionMethodSql()
    {
        return "INSERT INTO `instruction_method` (`id`, `name`, `created_at`, `updated_at`, `status`) VALUES
        (1, '写生', 1463987935, 1463987935, 10),
        (2, '画照片', 1463987935, 1463987935, 10),
        (3, '临摹', 1463987935, 1463987935, 10),
        (4, '直播示范', 1463987935, 1463987935, 10),
        (5, '讲大课', 1463987935, 1463987935, 10),
        (6, '自习', 1463987935, 1463987935, 10),
        (7, '考试', 1463987935, 1463987935, 10);";
    }
}