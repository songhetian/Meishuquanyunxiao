<?php

use yii\db\Migration;

class m160517_081005_user extends Migration
{
    const TBL_NAME = '{{%user}}';

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
            'student_id' => $this->bigInteger()->notNull()->comment('学号'),
            'campus_id' => $this->smallInteger()->notNull()->comment('所属校区'),
            'class_id' => $this->smallInteger()->comment('所在班级'),
            'name' => $this->string(32)->notNull()->comment('真实姓名'),
            'phone_number' => $this->string(11)->notNull()->unique()->comment('手机号'),
            'password_hash' => $this->string(100)->notNull()->comment('密码'),
            
            'gender' => $this->smallInteger()->comment('性别'),
            'national_id' => $this->string(18)->comment('身份证号码'),
            'family_member_name' => $this->string(32)->comment('家庭成员姓名'),
            'relationship' => $this->smallInteger()->comment('与本人关系'),
            'organization' => $this->string(32)->comment('单位'),
            'position' => $this->string(32)->comment('职务'),
            'contact_phone' => $this->string(11)->comment('联系电话'),
            
            'race' => $this->smallInteger()->comment('民族'),
            'student_type' => $this->smallInteger()->comment('考生类别'),
            'career_pursuit_type' => $this->smallInteger()->comment('文理科'),
            'residence_type' => $this->smallInteger()->comment('户口类别'),
            'grade' => $this->smallInteger()->comment('年级'),
            'province' => $this->smallInteger()->comment('省'),
            'city' => $this->smallInteger()->comment('市'),
            'detailed_address' => $this->string(50)->comment('详细住址'),
            'qq_number' => $this->string(11)->comment('QQ号码'),
            'school_name' => $this->string(32)->comment('就读学校'),
            'united_exam_province' => $this->smallInteger()->comment('联考省份'),
            'fine_art_instructor' => $this->string(32)->comment('高中美术老师'),

            'exam_participant_number' => $this->string(14)->comment('考生号'),
            'sketch_score' => $this->float()->comment('素描'),
            'color_score' => $this->float()->comment('色彩'),
            'quick_sketch_score' => $this->float()->comment('速写'),
            'design_score' => $this->float()->comment('设计'),
            'verbal_score' => $this->float()->comment('语文'),
            'math_score' => $this->float()->comment('数学'),
            'english_score' => $this->float()->comment('英语'),
            'total_score' =>$this->float()->comment('综合'),
            'pre_school_assessment' => $this->text()->comment('入学测试评估'),
            'credit' => $this->smallInteger()->notNull()->defaultValue(100)->comment('学分'),

            'is_graduation' => $this->smallInteger()->notNull()->defaultValue(0)->comment('是否毕业'),
            'graduation_at' => $this->integer()->comment('毕业时间'),
            'is_all_visible' => $this->smallInteger()->notNull()->defaultValue(0)->comment('是否全部可见'),
            'note' => $this->text()->comment('备注'),
            
            'auth_key' => $this->string(32)->notNull()->comment('认证密钥'),
            'password_reset_token' => $this->string(100)->unique()->comment('密码重置Token'),
            'device_token' => $this->string(100)->comment('设备令牌'),
            'access_token' => $this->string(32)->unique()->comment('访问令牌'),
            'admin_id' => $this->integer()->comment('创建者'),
            'token_value' => $this->string(32)->comment('Token值'),
            
            'created_at' => $this->integer()->notNull()->comment('创建时间'),
            'updated_at' => $this->integer()->notNull()->comment('更新时间'),
            'is_review' => $this->smallInteger()->notNull()->defaultValue(0)->comment('是否审核'),
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