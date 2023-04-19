<?php

use yii\db\Migration;

class m170225_174347_message_category extends Migration
{
    const TBL_NAME = '{{%message_category}}';

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
            'icon' => $this->string(200)->comment('图标'),
            'priority' => $this->integer()->notNull()->defaultValue(0)->comment('优先级'),

            'created_at' => $this->integer()->notNull()->comment('创建时间'),
            'updated_at' => $this->integer()->notNull()->comment('更新时间'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('状态'),
        ], $this->tableOptions);
        $this->execute($this->getMessageCategorySql());
    }

    private function delTable()
    {
        return "DROP TABLE IF EXISTS ".self::TBL_NAME.";";
    }

    public function safeDown()
    {
        $this->dropTable(self::TBL_NAME);
    }

    private function getMessageCategorySql()
    {
        return "INSERT INTO `message_category` (`id`, `name`, `icon`, `priority`, `created_at`, `updated_at`, `status`) VALUES
        (1, '我的消息', 'my_msg.png', 1, 1463987935, 1463987935, 10),
        (2, '学校通知', 'campus_msg.png', 2, 1463987935, 1463987935, 10),
        (3, '系统消息', 'system_msg.png', 3, 1463987935, 1463987935, 10);";
    }
}