<?php

use yii\db\Migration;

class m170215_033225_picture extends Migration
{
    const TBL_NAME = '{{%picture}}';

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
            'source' => $this->smallInteger()->notNull()->defaultValue(10)->comment('来源'),
            'name' => $this->text()->comment('名称'),
            'metis_material_id' => $this->integer()->comment('美术圈素材ID'),
            'publishing_company' => $this->integer()->comment('出版社'),
            'category_id' => $this->smallInteger()->notNull()->comment('分类'),
            'keyword_id' => $this->string(100)->comment('关键字'),
            'image' => $this->string(200)->comment('图片'),
            'watch_count' => $this->integer()->notNull()->defaultValue(0)->comment('查看次数'),
            'admin_id' => $this->integer()->notNull()->comment('上传者'),
            'is_public' => $this->smallInteger()->notNull()->defaultValue(10)->comment('是否公开'),
            
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