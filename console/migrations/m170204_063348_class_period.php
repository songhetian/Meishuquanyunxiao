<?php

use yii\db\Migration;

class m170204_063348_class_period extends Migration
{
    const TBL_NAME = '{{%class_period}}';

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
            'name' => $this->string(32)->notNull()->comment('名称'),
            'studio_id' => $this->smallInteger()->notNull()->comment('所属画室'),
            'started_at' => $this->string(32)->notNull()->comment('上课时间'),
            'dismissed_at' => $this->string(32)->notNull()->comment('下课时间'),
            'position' => $this->smallInteger()->notNull()->comment('位置'),
            'created_at' => $this->integer()->notNull()->comment('创建时间'),
            'updated_at' => $this->integer()->notNull()->comment('更新时间'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('状态'),
        ], $this->tableOptions);
        $this->execute($this->getClassPeriodSql());
    }

    private function delTable()
    {
        return "DROP TABLE IF EXISTS ".self::TBL_NAME.";";
    }

    public function safeDown()
    {
        $this->dropTable(self::TBL_NAME);
    }

    private function getClassPeriodSql()
    {
        return "INSERT INTO `class_period` (`id`, `name`, `studio_id`, `started_at`, `dismissed_at`, `position`, `created_at`, `updated_at`, `status`) VALUES
        (1, '早课', 1, '07:00', '08:00', 1, 1486201416, 1486201416, 10),
        (2, '上午', 1, '08:30', '11:30', 2, 1486201416, 1486201416, 10),
        (3, '下午', 1, '13:00', '17:00', 3, 1486201416, 1486201416, 10),
        (4, '晚上', 1, '18:00', '21:00', 4, 1486201416, 1486201416, 10),
        (5, '加课', 1, '21:30', '00:00', 5, 1486201416, 1486201416, 10)";
    }
}