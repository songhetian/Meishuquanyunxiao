<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\base\InvalidConfigException;
use yii\rbac\DbManager;

/**
 * Initializes RBAC tables
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class m140506_102106_rbac_init extends \yii\db\Migration
{
    /**
     * @throws yii\base\InvalidConfigException
     * @return DbManager
     */
    protected function getAuthManager()
    {
        $authManager = Yii::$app->getAuthManager();
        if (!$authManager instanceof DbManager) {
            throw new InvalidConfigException('你应该配置 authManager 使用数据库再执行迁移');
        }
        return $authManager;
    }

    /**
     * @return bool
     */
    protected function isMSSQL()
    {
        return $this->db->driverName === 'mssql' || $this->db->driverName === 'sqlsrv' || $this->db->driverName === 'dblib';
    }

    public function up()
    {
        $authManager = $this->getAuthManager();
        $this->db = $authManager->db;

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($authManager->ruleTable, [
            'name' => $this->string(64)->notNull()->comment('名称'),
            'data' => $this->text()->comment('数据'),
            'created_at' => $this->integer()->notNull()->comment('创建时间'),
            'updated_at' => $this->integer()->notNull()->comment('更新时间'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('状态'),
            'PRIMARY KEY (name)',
        ], $tableOptions);

        $this->createTable($authManager->itemTable, [
            'name' => $this->string(64)->notNull()->comment('名称'),
            'pid' => $this->integer()->notNull()->defaultValue(0)->comment('上级'),
            'studio_id' => $this->smallInteger()->notNull()->comment('所属画室'),
            'type' => $this->integer()->notNull()->comment('类型'),
            'scope' =>  $this->string(64)->comment('生效范围'),
            'description' => $this->string(64)->notNull()->comment('职位名称'),
            'rule_name' => $this->string(64)->comment('规则名称'),
            'data' => $this->text()->comment('数据'),

            'created_at' => $this->integer()->notNull()->comment('创建时间'),
            'updated_at' => $this->integer()->notNull()->comment('更新时间'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('状态'),

            'PRIMARY KEY (name)',
            'FOREIGN KEY (rule_name) REFERENCES ' . $authManager->ruleTable . ' (name)'.
                ($this->isMSSQL() ? '' : ' ON DELETE SET NULL ON UPDATE CASCADE'),
        ], $tableOptions);
        $this->createIndex('idx-auth_item-type', $authManager->itemTable, 'type');

        $this->createTable($authManager->itemChildTable, [
            'parent' => $this->string(64)->notNull()->comment('父类'),
            'child' => $this->string(64)->notNull()->comment('子类'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('状态'),
            'PRIMARY KEY (parent, child)',
            'FOREIGN KEY (parent) REFERENCES ' . $authManager->itemTable . ' (name)'.
                ($this->isMSSQL() ? '' : ' ON DELETE CASCADE ON UPDATE CASCADE'),
            'FOREIGN KEY (child) REFERENCES ' . $authManager->itemTable . ' (name)'.
                ($this->isMSSQL() ? '' : ' ON DELETE CASCADE ON UPDATE CASCADE'),
        ], $tableOptions);

        $this->createTable($authManager->assignmentTable, [
            'item_name' => $this->string(64)->notNull()->comment('角色'),
            'user_id' => $this->string(64)->notNull()->comment('管理员'),
            'created_at' => $this->integer()->notNull()->comment('创建时间'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('状态'),
            'PRIMARY KEY (item_name, user_id)',
            'FOREIGN KEY (item_name) REFERENCES ' . $authManager->itemTable . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
        ], $tableOptions);

        if ($this->isMSSQL()) {
            $this->execute("CREATE TRIGGER dbo.trigger_auth_item_child
            ON dbo.{$authManager->itemTable}
            INSTEAD OF DELETE, UPDATE
            AS
            DECLARE @old_name VARCHAR (64) = (SELECT name FROM deleted)
            DECLARE @new_name VARCHAR (64) = (SELECT name FROM inserted)
            BEGIN
            IF COLUMNS_UPDATED() > 0
                BEGIN
                    IF @old_name <> @new_name
                    BEGIN
                        ALTER TABLE auth_item_child NOCHECK CONSTRAINT FK__auth_item__child;
                        UPDATE auth_item_child SET child = @new_name WHERE child = @old_name;
                    END
                UPDATE auth_item
                SET name = (SELECT name FROM inserted),
                type = (SELECT type FROM inserted),
                description = (SELECT description FROM inserted),
                rule_name = (SELECT rule_name FROM inserted),
                data = (SELECT data FROM inserted),
                created_at = (SELECT created_at FROM inserted),
                updated_at = (SELECT updated_at FROM inserted)
                WHERE name IN (SELECT name FROM deleted)
                IF @old_name <> @new_name
                    BEGIN
                        ALTER TABLE auth_item_child CHECK CONSTRAINT FK__auth_item__child;
                    END
                END
                ELSE
                    BEGIN
                        DELETE FROM dbo.{$authManager->itemChildTable} WHERE parent IN (SELECT name FROM deleted) OR child IN (SELECT name FROM deleted);
                        DELETE FROM dbo.{$authManager->itemTable} WHERE name IN (SELECT name FROM deleted);
                    END
            END;");
        }
        //添加默认数据
        $this->execute($this->getItemRole());
        $this->execute($this->getItem());

        $this->execute($this->getItemChild());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $authManager = $this->getAuthManager();
        $this->db = $authManager->db;

        if ($this->isMSSQL()) {
            $this->execute('DROP TRIGGER dbo.trigger_auth_item_child;');
        }

        $this->dropTable($authManager->assignmentTable);
        $this->dropTable($authManager->itemChildTable);
        $this->dropTable($authManager->itemTable);
        $this->dropTable($authManager->ruleTable);
    }

    //添加权限
    private function getItem()
    {
        return "INSERT INTO `auth_item` (`name`, `pid`, `studio_id`, `type`, `scope`, `description`, `rule_name`, `data`, `created_at`, `updated_at`, `status`) VALUES
        ('site', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Index')."', NULL, NULL, 1480521600, 1480521600, 10),

        ('campus', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Campuses')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('campus/create', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Create Campus')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('campus/view', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'View Campus')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('campus/update', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Update Campus')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('campus/delete', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Delete Campus')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('campus/recovery', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Recovery Campus')."', NULL, NULL, 1480521600, 1480521600, 10),
        
        ('classes', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Classes')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('classes/create', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Create Classes')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('classes/view', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'View Classes')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('classes/update', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Update Classes')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('classes/delete', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Delete Classes')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('classes/recovery', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Recovery Classes')."', NULL, NULL, 1480521600, 1480521600, 10),

        ('admin', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Admins')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('admin/create', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Create Admin')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('admin/view', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'View Admin')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('admin/update', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Update Admin')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('admin/is-all-visible', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Visible Admin')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('admin/delete', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Delete Admin')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('admin/recovery', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Recovery Admin')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('admin/export', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Export Admin')."', NULL, NULL, 1480521600, 1480521600, 10),

        ('course', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Courses')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('course/create', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Create Course')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('course/view', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'View Course')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('course/update', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Update Course')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('course/delete', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Delete Course')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('course/recovery', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Recovery Course')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('course/export', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Export Course')."', NULL, NULL, 1480521600, 1480521600, 10),

        ('class-period', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Class Periods')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('class-period/create', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Create Period')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('class-period/view', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'View Period')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('class-period/update', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Update Period')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('class-period/delete', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Delete Period')."', NULL, NULL, 1480521600, 1480521600, 10),

        ('course-material', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Course Materials')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('course-material/create', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Create Material')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('course-material/view', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'View Material')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('course-material/update', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Update Material')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('course-material/delete', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Delete Material')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('course-material/recovery', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Recovery Material')."', NULL, NULL, 1480521600, 1480521600, 10),

        ('picture', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Pictures')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('picture/create', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Create Picture')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('picture/view', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'View Picture')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('picture/update', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Update Picture')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('picture/delete', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Delete Picture')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('picture/recovery', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Recovery Picture')."', NULL, NULL, 1480521600, 1480521600, 10),

        ('video', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Videos')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('video/create', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Create Video')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('video/view', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'View Video')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('video/update', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Update Video')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('video/delete', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Delete Video')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('video/recovery', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Recovery Video')."', NULL, NULL, 1480521600, 1480521600, 10),


        ('user', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Users')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('user/create', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Create User')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('user/view', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'View User')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('user/update', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Update User')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('user/is-all-visible', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Visible User')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('user/is-review', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Review User')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('user/delete', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Delete User')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('user/recovery', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Recovery User')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('user/export', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Export User')."', NULL, NULL, 1480521600, 1480521600, 10),
        
        ('user-homework', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'User Homeworks')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('user-homework/view', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'View Homework')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('user-homework/update', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Update Homework')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('user-homework/delete', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Delete Homework')."', NULL, NULL, 1480521600, 1480521600, 10),

        ('message', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Messages')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('message/create', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Create Message')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('message/view', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'View Message')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('message/delete', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Delete Message')."', NULL, NULL, 1480521600, 1480521600, 10),

        ('menu', NULL, NULL, 2, 20,'".Yii::t('backend', 'Menus')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('menu/create', NULL, NULL, 2, 20,'".Yii::t('backend', 'Create Menu')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('menu/view', NULL, NULL, 2, 20,'".Yii::t('backend', 'View Menu')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('menu/update', NULL, NULL, 2, 20,'".Yii::t('backend', 'Update Menu')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('menu/delete', NULL, NULL, 2, 20,'".Yii::t('backend', 'Delete Menu')."', NULL, NULL, 1480521600, 1480521600, 10),

        ('role', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Roles')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('role/create', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Create Role')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('role/view', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'View Role')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('role/update', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Update Role')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('role/delete', NULL, NULL, 2, '10,20', '".Yii::t('backend', 'Delete Role')."', NULL, NULL, 1480521600, 1480521600, 10),

        ('admin-log', NULL, NULL, 2, 20,'".Yii::t('backend', 'Admin Logs')."', NULL, NULL, 1480521600, 1480521600, 10),
        ('admin-log/view', NULL, NULL, 2, 20,'".Yii::t('backend', 'View Log')."', NULL, NULL, 1480521600, 1480521600, 10);";
    }

    //添加角色
    private function getItemRole()
    {
        return "INSERT INTO `auth_item` (`name`, `pid`, `studio_id`, `type`, `scope`, `description`, `rule_name`, `data`, `created_at`, `updated_at`, `status`) VALUES
        ('170001001', 0, 1, 1, NULL, '".Yii::t('backend', 'Principal')."', NULL, NULL, 1481644800, 1481644800, 10),
        ('170001002', 170001001, 1, 1, NULL, '".Yii::t('backend', 'Teaching Principle')."', NULL, NULL, 1481644860, 1481644860, 10),
        ('170001003', 170001002, 1, 1, NULL, '".Yii::t('backend', 'Teaching Supervisor')."', NULL, NULL, 1481644920, 1481644920, 10),
        ('170001004', 170001003, 1, 1, NULL, '".Yii::t('backend', 'Class Supervisor')."', NULL, NULL, 1481644980, 1481644980, 10),
        ('170001005', 170001004, 1, 1, NULL, '".Yii::t('backend', 'Teacher')."', NULL, NULL, 1481645040, 1481645040, 10),
        ('170001006', 170001005, 1, 1, NULL, '".Yii::t('backend', 'Teaching Assistant')."', NULL, NULL, 1481645100, 1481645100, 10)";
    }

    //设置角色拥有的权限
    private function getItemChild()
    {
        return "INSERT INTO `auth_item_child` (`parent`, `child`, `status`) VALUES
        ('170001001', 'site', 10),

        ('170001001', 'campus', 10),
        ('170001001', 'campus/create', 10),
        ('170001001', 'campus/view', 10),
        ('170001001', 'campus/update', 10),
        ('170001001', 'campus/delete', 10),
        ('170001001', 'campus/recovery', 10),

        ('170001001', 'classes', 10),
        ('170001001', 'classes/create', 10),
        ('170001001', 'classes/view', 10),
        ('170001001', 'classes/update', 10),
        ('170001001', 'classes/delete', 10),        
        ('170001001', 'classes/recovery', 10),

        ('170001001', 'admin', 10),
        ('170001001', 'admin/create', 10),
        ('170001001', 'admin/view', 10),
        ('170001001', 'admin/update', 10),
        ('170001001', 'admin/is-all-visible', 10),
        ('170001001', 'admin/delete', 10),
        ('170001001', 'admin/recovery', 10),
        ('170001001', 'admin/export', 10),

        ('170001001', 'course', 10),
        ('170001001', 'course/create', 10),
        ('170001001', 'course/view', 10),
        ('170001001', 'course/update', 10),
        ('170001001', 'course/delete', 10),
        ('170001001', 'course/recovery', 10),
        ('170001001', 'course/export', 10),
       
        ('170001001', 'class-period', 10),
        ('170001001', 'class-period/create', 10),
        ('170001001', 'class-period/view', 10),
        ('170001001', 'class-period/update', 10),
        ('170001001', 'class-period/delete', 10),

        ('170001001', 'course-material', 10),
        ('170001001', 'course-material/create', 10),
        ('170001001', 'course-material/view', 10),
        ('170001001', 'course-material/update', 10),
        ('170001001', 'course-material/delete', 10),
        ('170001001', 'course-material/recovery', 10),

        ('170001001', 'picture', 10),
        ('170001001', 'picture/create', 10),
        ('170001001', 'picture/view', 10),
        ('170001001', 'picture/update', 10),
        ('170001001', 'picture/delete', 10),
        ('170001001', 'picture/recovery', 10),

        ('170001001', 'video', 10),
        ('170001001', 'video/create', 10),
        ('170001001', 'video/view', 10),
        ('170001001', 'video/update', 10),
        ('170001001', 'video/delete', 10),
        ('170001001', 'video/recovery', 10),
       
        ('170001001', 'user', 10),
        ('170001001', 'user/create', 10),
        ('170001001', 'user/view', 10),
        ('170001001', 'user/update', 10),
        ('170001001', 'user/is-all-visible', 10),
        ('170001001', 'user/is-review', 10),
        ('170001001', 'user/delete', 10),
        ('170001001', 'user/recovery', 10),
        ('170001001', 'user/export', 10),

        ('170001001', 'user-homework', 10),
        ('170001001', 'user-homework/view', 10),
        ('170001001', 'user-homework/update', 10),
        ('170001001', 'user-homework/delete', 10),

        ('170001001', 'message', 10),
        ('170001001', 'message/create', 10),
        ('170001001', 'message/view', 10),
        ('170001001', 'message/delete', 10),

        ('170001001', 'role', 10),
        ('170001001', 'role/create', 10),
        ('170001001', 'role/view', 10),
        ('170001001', 'role/update', 10),
        ('170001001', 'role/delete', 10),
        /**********************************/
        ('170001002', 'site', 10),

        ('170001002', 'campus', 10),
        ('170001002', 'campus/view', 10),
        ('170001002', 'campus/update', 10),

        ('170001002', 'classes', 10),
        ('170001002', 'classes/create', 10),
        ('170001002', 'classes/view', 10),
        ('170001002', 'classes/update', 10),
        ('170001002', 'classes/delete', 10),        
       
        ('170001002', 'admin', 10),
        ('170001002', 'admin/create', 10),
        ('170001002', 'admin/view', 10),
        ('170001002', 'admin/update', 10),
        ('170001002', 'admin/is-all-visible', 10),
        ('170001002', 'admin/delete', 10),

        ('170001002', 'course', 10),
        ('170001002', 'course/create', 10),
        ('170001002', 'course/view', 10),
        ('170001002', 'course/update', 10),
        ('170001002', 'course/delete', 10),
       
        ('170001002', 'class-period', 10),
        ('170001002', 'class-period/create', 10),
        ('170001002', 'class-period/view', 10),
        ('170001002', 'class-period/update', 10),
        ('170001002', 'class-period/delete', 10),

        ('170001002', 'course-material', 10),
        ('170001002', 'course-material/create', 10),
        ('170001002', 'course-material/view', 10),
        ('170001002', 'course-material/update', 10),
        ('170001002', 'course-material/delete', 10),

        ('170001002', 'picture', 10),
        ('170001002', 'picture/create', 10),
        ('170001002', 'picture/view', 10),
        ('170001002', 'picture/update', 10),
        ('170001002', 'picture/delete', 10),

        ('170001002', 'video', 10),
        ('170001002', 'video/create', 10),
        ('170001002', 'video/view', 10),
        ('170001002', 'video/update', 10),
        ('170001002', 'video/delete', 10),
       
        ('170001002', 'user', 10),
        ('170001002', 'user/create', 10),
        ('170001002', 'user/view', 10),
        ('170001002', 'user/update', 10),
        ('170001002', 'user/is-all-visible', 10),
        ('170001002', 'user/is-review', 10),
        ('170001002', 'user/delete', 10),

        ('170001002', 'user-homework', 10),
        ('170001002', 'user-homework/view', 10),
        ('170001002', 'user-homework/update', 10),
        ('170001002', 'user-homework/delete', 10),

        ('170001002', 'message', 10),
        ('170001002', 'message/create', 10),
        ('170001002', 'message/view', 10),
        ('170001002', 'message/delete', 10),
        /*******************************************/
        ('170001003', 'site', 10),
       
        ('170001003', 'classes', 10),
        ('170001003', 'classes/create', 10),
        ('170001003', 'classes/view', 10),
        ('170001003', 'classes/update', 10),

        ('170001003', 'admin', 10),
        ('170001003', 'admin/create', 10),
        ('170001003', 'admin/view', 10),
        ('170001003', 'admin/update', 10),
        ('170001003', 'admin/delete', 10),

        ('170001003', 'course', 10),
        ('170001003', 'course/create', 10),
        ('170001003', 'course/view', 10),
        ('170001003', 'course/update', 10),
        ('170001003', 'course/delete', 10),
       
        ('170001003', 'course-material', 10),
        ('170001003', 'course-material/create', 10),
        ('170001003', 'course-material/view', 10),
        ('170001003', 'course-material/update', 10),
        ('170001003', 'course-material/delete', 10),

        ('170001003', 'picture', 10),
        ('170001003', 'picture/create', 10),
        ('170001003', 'picture/view', 10),
        ('170001003', 'picture/update', 10),
        ('170001003', 'picture/delete', 10),

        ('170001003', 'video', 10),
        ('170001003', 'video/create', 10),
        ('170001003', 'video/view', 10),
        ('170001003', 'video/update', 10),
        ('170001003', 'video/delete', 10),
       
        ('170001003', 'user', 10),
        ('170001003', 'user/create', 10),
        ('170001003', 'user/view', 10),
        ('170001003', 'user/update', 10),
        ('170001003', 'user/is-all-visible', 10),
        ('170001003', 'user/is-review', 10),
        ('170001003', 'user/delete', 10),

        ('170001003', 'user-homework', 10),
        ('170001003', 'user-homework/view', 10),
        ('170001003', 'user-homework/update', 10),
        ('170001003', 'user-homework/delete', 10),

        ('170001003', 'message', 10),
        ('170001003', 'message/create', 10),
        ('170001003', 'message/view', 10),
        /********************************************************/
        ('170001004', 'site', 10),
       
        ('170001004', 'admin', 10),
        ('170001004', 'admin/view', 10),
        ('170001004', 'admin/update', 10),

        ('170001004', 'course', 10),
        ('170001004', 'course/create', 10),
        ('170001004', 'course/view', 10),
        ('170001004', 'course/update', 10),
        ('170001004', 'course/delete', 10),
       
        ('170001004', 'course-material', 10),
        ('170001004', 'course-material/create', 10),
        ('170001004', 'course-material/view', 10),
        ('170001004', 'course-material/update', 10),
        ('170001004', 'course-material/delete', 10),

        ('170001004', 'picture', 10),
        ('170001004', 'picture/create', 10),
        ('170001004', 'picture/view', 10),
        ('170001004', 'picture/update', 10),
        ('170001004', 'picture/delete', 10),

        ('170001004', 'video', 10),
        ('170001004', 'video/create', 10),
        ('170001004', 'video/view', 10),
        ('170001004', 'video/update', 10),
        ('170001004', 'video/delete', 10),
       
        ('170001004', 'user', 10),
        ('170001004', 'user/create', 10),
        ('170001004', 'user/view', 10),
        ('170001004', 'user/update', 10),
        ('170001004', 'user/is-all-visible', 10),
        ('170001004', 'user/is-review', 10),
        ('170001004', 'user/delete', 10),

        ('170001004', 'user-homework', 10),
        ('170001004', 'user-homework/view', 10),
        ('170001004', 'user-homework/update', 10),
        ('170001004', 'user-homework/delete', 10),
       
        ('170001004', 'message', 10),
        ('170001004', 'message/view', 10),
        /**************************************/
        ('170001005', 'site', 10),

        ('170001005', 'admin', 10),
        ('170001005', 'admin/view', 10),
        ('170001005', 'admin/update', 10),

        ('170001005', 'course', 10),
        ('170001005', 'course/create', 10),
        ('170001005', 'course/view', 10),
        ('170001005', 'course/update', 10),
        ('170001005', 'course/delete', 10),
       
        ('170001005', 'course-material', 10),
        ('170001005', 'course-material/create', 10),
        ('170001005', 'course-material/view', 10),
        ('170001005', 'course-material/update', 10),
        ('170001005', 'course-material/delete', 10),

        ('170001005', 'picture', 10),
        ('170001005', 'picture/create', 10),
        ('170001005', 'picture/view', 10),
        ('170001005', 'picture/update', 10),
        ('170001005', 'picture/delete', 10),

        ('170001005', 'video', 10),
        ('170001005', 'video/create', 10),
        ('170001005', 'video/view', 10),
        ('170001005', 'video/update', 10),
        ('170001005', 'video/delete', 10),

        ('170001005', 'user', 10),
        ('170001005', 'user/create', 10),
        ('170001005', 'user/view', 10),
        ('170001005', 'user/update', 10),
        ('170001005', 'user/is-all-visible', 10),
        ('170001005', 'user/is-review', 10),
        ('170001005', 'user/delete', 10),

        ('170001005', 'user-homework', 10),
        ('170001005', 'user-homework/view', 10),
        ('170001005', 'user-homework/update', 10),
        ('170001005', 'user-homework/delete', 10),
       
        ('170001005', 'message', 10),
        ('170001005', 'message/view', 10),
        /***********************************************/
        ('170001006', 'site', 10),

        ('170001006', 'admin', 10),
        ('170001006', 'admin/view', 10),
        ('170001006', 'admin/update', 10),
        
        ('170001006', 'course', 10),
        ('170001006', 'course/create', 10),
        ('170001006', 'course/view', 10),
        ('170001006', 'course/update', 10),
        ('170001006', 'course/delete', 10),
       
        ('170001006', 'course-material', 10),
        ('170001006', 'course-material/create', 10),
        ('170001006', 'course-material/view', 10),
        ('170001006', 'course-material/update', 10),
        ('170001006', 'course-material/delete', 10),

        ('170001006', 'picture', 10),
        ('170001006', 'picture/create', 10),
        ('170001006', 'picture/view', 10),
        ('170001006', 'picture/update', 10),
        ('170001006', 'picture/delete', 10),

        ('170001006', 'video', 10),
        ('170001006', 'video/create', 10),
        ('170001006', 'video/view', 10),
        ('170001006', 'video/update', 10),
        ('170001006', 'video/delete', 10),

        ('170001006', 'user', 10),
        ('170001006', 'user/create', 10),
        ('170001006', 'user/view', 10),
        ('170001006', 'user/update', 10),
        ('170001006', 'user/is-all-visible', 10),
        ('170001006', 'user/is-review', 10),
        ('170001006', 'user/delete', 10),

        ('170001006', 'user-homework', 10),
        ('170001006', 'user-homework/view', 10),
        ('170001006', 'user-homework/update', 10),
        ('170001006', 'user-homework/delete', 10),
       
        ('170001006', 'message', 10),
        ('170001006', 'message/view', 10);";
    }
}