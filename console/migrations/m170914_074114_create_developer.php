<?php

use yii\db\Migration;
use yii\helpers\Console;

class m170914_074114_create_developer extends Migration
{
    public function safeUp()
    {
        $this->createDeveloper();
    }

    public function createDeveloper()
    {
        Console::output("\n请先创建管理员账户:   ");
        $developer = $this->saveDeveloperData(new \developer\models\Developer(['scenario' => 'create']));
        $developer ? $developer->id : 1;
        Console::output("管理员创建" . ($developer ? '成功' : "失败,请手动创建管理员用户\n"));
    }

    /**
     * 管理员创建交互
     * @param $_model
     * @return mixed
     */
    private function saveDeveloperData($_model)
    {
        $model = clone $_model;
        $model->phone_number = Console::prompt('请输入手机号', ['default' => '17611147217']);
        $model->password_hash = Console::prompt('请输入密码', ['default' => '123456']);
        $model->name = Console::prompt('请输入昵称', ['default' => '佟飞']);
        if (!$model->consoleSignup()) {
            Console::output(Console::ansiFormat("\n输入数据验证错误:", [Console::FG_RED]));
            foreach ($model->getErrors() as $value) {
                Console::output(Console::ansiFormat(implode("\n", $value), [Console::FG_RED]));
            }
            if (Console::confirm("\n是否重新创建管理员账户:")) {
                $model = $this->saveAdminData($_model);
            }
        }
        return $model;
    }
}