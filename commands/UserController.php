<?php


namespace app\commands;


use app\models\User;
use Yii;

class UserController extends \yii\console\Controller
{
    /**
     * Create user with admin privileges
     */
    public function actionCreateAdmin()
    {
        if (empty(User::findOne(['username' => 'github_admin']))) {
            $user = new User([
                'username'  => 'github_admin',
                'email'     => Yii::$app->params['adminEmail'],
            ]);

            $password = readline("password: ");
            $user->setPassword($password);
            $user->generateAuthKey();

            if ($user->save(false)) {
                echo "CREATED USER\n";
            }

            //add role
            $auth = Yii::$app->authManager;
            $role = $auth->getRole(User::ROLE_ADMIN);
            if ($auth->assign($role, $user->getId())) {
                echo "CREATED ROLE\n";
            }
        }
    }
}