<?php


namespace app\commands;


use Exception;
use yii\console\Controller;
use yii\console\ExitCode;

class RbacController extends Controller
{
    /**
     * @return int ExitCode
     * @throws Exception
     */
    public function actionInit(): int
    {
        $authManager = \Yii::$app->authManager;

        // Create roles
        $user = $authManager->createRole('user');
        $admin = $authManager->createRole('admin');

        try {
            $authManager->add($user);
            $authManager->add($admin);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        return ExitCode::OK;
    }
}