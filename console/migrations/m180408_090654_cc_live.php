<?php

use yii\db\Migration;

class m180408_090654_cc_live extends Migration
{
    const TBL_NAME = '{{%cc_live}}';

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
            'cc_id' => $this->string(200)->comment('CCID'),
            'title' => $this->string(200)->comment('名称'),
            'description' => $this->string(200)->comment('描述'),
            'user_id' => $this->integer()->notNull()->comment('用户ID'),
            'templatetype' => $this->integer()->defaultValue(2)->comment('类型'),
            'authtype' => $this->integer()->defaultValue(2)->comment('验证方式'),
            'publisherpass' => $this->string(200)->comment('推流端密码'),
            'assistantpass' => $this->string(200)->comment('助教端密码'),
            'playpass' => $this->integer()->comment('播放端密码'),
            'checkurl' => $this->string(200)->comment('验证地址'),
            'barrage' => $this->integer()->defaultValue(1)->comment('是否开启弹幕'),
            'foreignpublish' => $this->integer()->defaultValue(0)->comment('是否开启第三方推流'),
            'openlowdelaymode' => $this->integer()->defaultValue(1)->comment('开启直播低延时模式'),
            'showusercount' => $this->integer()->defaultValue(1)->comment('显示当前在线人数'),
            'is_recommend' => $this->integer()->defaultValue(1)->comment('推荐状态'),
            
            'publish_url' => $this->string(200)->comment('推流地址'),
            'pic_url' => $this->string(200)->comment('封面'),
            'play_status' => $this->integer()->defaultValue(1)->comment('直播状态'),
            'play_type' => $this->string(100)->defaultValue('phone')->comment('是否手机直播'),
            'tactics_id' => $this->integer()->defaultValue(1)->comment('策略'),
            'is_sideways' => $this->integer()->comment('是否为横屏直播'),
            'studio_id' => $this->integer()->comment('所属画室'),

            'start_time' => $this->timestamp()->defaultValue(NULL)->comment('开始时间'),
            'end_time' => $this->timestamp()->defaultValue(NULL)->comment('结束时间'),

            'created_at' => $this->integer()->notNull()->comment('创建时间'),
            'updated_at' => $this->integer()->notNull()->comment('更新时间'),
            'status' => $this->smallInteger()->notNull()->defaultValue(1)->comment('状态'),
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
