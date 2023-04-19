<?php

use yii\db\Migration;

class m180205_052357_feedback extends Migration
{
    const TBL_NAME = '{{%feedback}}';
    
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
            'type' => $this->string(32)->notNull()->comment('类型'),

            'manufacturer_model' => $this->string(100)->comment('设备信息'),
            'system_version' => $this->string(100)->comment('系统版本'),
            'app_version' => $this->string(100)->comment('软件版本'),
            'network_state' => $this->string(100)->comment('网络状态'),
            'longitude' => $this->string(100)->comment('精度'),
            'latitude' => $this->string(100)->comment('纬度'),

            'content' => $this->text()->comment('内容'),
            'image' => $this->string(200)->comment('图片'),
            'contact' => $this->string(32)->notNull()->comment('联系方式'),
            'feedback_id' => $this->integer()->notNull()->comment('反馈人'),
            
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
