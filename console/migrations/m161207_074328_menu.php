<?php

use yii\db\Migration;

class m161207_074328_menu extends Migration
{
    const TBL_NAME = '{{%menu}}';

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
            'name' => $this->string(32)->notNull()->unique()->comment('名称'),
            'pid' => $this->integer()->notNull()->defaultValue(0)->comment('父菜单'),
            'route' => $this->string(32)->comment('路由'),
            'icon' => $this->string(32)->comment('图标'),
            'priority' => $this->integer()->notNull()->defaultValue(0)->comment('优先级'),

            'created_at' => $this->integer()->notNull()->comment('创建时间'),
            'updated_at' => $this->integer()->notNull()->comment('更新时间'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('状态'),
        ], $this->tableOptions);
        $this->execute($this->getMenuSql());
    }

    private function delTable()
    {
        return "DROP TABLE IF EXISTS ".self::TBL_NAME.";";
    }

    public function safeDown()
    {
        $this->dropTable(self::TBL_NAME);
    }
    
    private function getMenuSql()
    {
        return "INSERT INTO `menu` (`id`, `name`, `pid`, `route`, `icon`, `priority`, `created_at`, `updated_at`, `status`) VALUES
        (1, '".Yii::t('backend', 'Index Management')."', 0, NULL, 'fa-tachometer', 1,  1480521600, 1480521600, 10),
        (2, '".Yii::t('backend', 'Index')."', 1, 'site', NULL, 1,  1480521600, 1480521600, 10),

        (3, '".Yii::t('backend', 'Campus Management')."', 0, NULL, 'fa-home', 2,  1480521600, 1480521600, 10),
        (4, '".Yii::t('backend', 'Campuses')."', 3, 'campus', NULL, 1,  1480521600, 1480521600, 10),
        (5, '".Yii::t('backend', 'Classes')."', 3, 'classes', NULL, 2,  1480521600, 1480521600, 0),
        (6, '".Yii::t('backend', 'Admins')."', 3, 'admin', NULL, 3,  1480521600, 1480521600, 0),

        (7, '".Yii::t('backend', 'Class Management')."', 0, NULL, 'fa-pencil', 3,  1480521600, 1480521600, 10),
        (8, '".Yii::t('backend', 'Courses')."', 7, 'course', NULL, 1,  1480521600, 1480521600, 10),
        (10, '".Yii::t('backend', 'Class Periods')."', 7, 'class-period', NULL, 3,  1480521600, 1480521600, 10),

        (11, '".Yii::t('backend', 'Course Material Management')."', 0, NULL, 'fa-book', 4,  1480521600, 1480521600, 10),
        (12, '".Yii::t('backend', 'Course Materials')."', 11, 'course-material', NULL, 1,  1480521600, 1480521600, 10),

        (13, '".Yii::t('backend', 'Material Library Management')."', 0, NULL, 'fa-camera', 5,  1480521600, 1480521600, 10),
        (14, '".Yii::t('backend', 'Pictures')."', 13, 'picture', NULL, 1,  1480521600, 1480521600, 10),
        (15, '".Yii::t('backend', 'Videos')."', 13, 'video', NULL, 2,  1480521600, 1480521600, 10),

        (17, '".Yii::t('backend', 'User Management')."', 0, NULL, 'fa-user', 6,  1480521600, 1480521600, 0),
        (18, '".Yii::t('backend', 'Users')."', 17, 'user', NULL, 1,  1480521600, 1480521600, 0),
        (19, '".Yii::t('backend', 'User Homeworks')."', 17, 'user-homework', NULL, 2,  1480521600, 1480521600, 0),

        (20, '".Yii::t('backend', 'System Management')."', 0, NULL, 'fa-cog', 7,  1480521600, 1480521600, 10),
        (21, '".Yii::t('backend', 'Messages')."', 20, 'message', NULL, 1,  1480521600, 1480521600, 10),
        (22, '".Yii::t('backend', 'Menus')."', 20, 'menu', NULL, 2, 1480521600, 1480521600, 10),
        (23, '".Yii::t('backend', 'Roles')."', 20, 'role', NULL, 3,  1480521600, 1480521600, 10),
        (24, '".Yii::t('backend', 'Admin Logs')."', 20, 'admin-log', NULL, 4, 1480521600, 1480521600, 10)";
    }
}