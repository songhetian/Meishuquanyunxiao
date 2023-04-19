<?php

use yii\db\Migration;

class m180403_031838_app extends Migration
{
    const TBL_NAME = '{{%app}}';
    
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
            'studio_id' => $this->smallInteger()->notNull()->comment('所属画室'),
            'ipa' => $this->string(200)->comment('IPA文件'),
            'plist' => $this->string(200)->comment('Plist文件'),
            'apk' => $this->string(200)->comment('APK文件'),
            'logo' => $this->string(200)->comment('Logo'),
            'android_version' => $this->string(32)->comment('安卓版本号'),
            'android_updated_details' => $this->text()->comment('安卓更新详情'),
            'ios_version' => $this->string(32)->comment('苹果版本号'),
            'ios_updated_details' => $this->text()->comment('苹果更新详情'),
            
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
