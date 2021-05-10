<?php


namespace app\modules\github\controllers;


use app\modules\github\components\GithubCache;
use app\modules\github\models\forms\UsersForm;
use app\modules\github\models\GithubUser;
use Yii;
use yii\db\StaleObjectException;
use yii\web\Controller;

class UserController extends Controller
{
    /**
     * delete old github users
     *
     * @param array|GithubUser[]    $users
     * @param string[]  $usernames  actual names list
     *
     * @return void
     */
    private function deleteOldUsers(array $users, array $usernames): void
    {
        foreach ($users as $user) {
            if (!in_array($user->name, $usernames)) {
                $user->delete();
            }
        }
    }

    /**
     * Update GithubUser models
     *
     * @return string
     */
    public function actionUpdate(): string
    {
        $usersForm  = new UsersForm();
        $users = GithubUser::getAll();

        if ($usersForm->load(Yii::$app->request->post())) {
            $usernames = explode("\r\n", $usersForm->users);

            $this->deleteOldUsers($users, $usernames);

            foreach ($usernames as $username) {
                if (empty(GithubUser::findOne(['name' => $username]))) {
                    $user = new GithubUser(['name' => $username]);
                    if (!$user->save()) {
                        Yii::error("Не удалось сохранить $username", 'update');
                    }
                }
            }
            GithubCache::flush();
            Yii::$app->session->setFlash('success', 'Сохранено');
        }
        $usersForm->users = implode("\r\n", GithubUser::getAllUsernames());

        return $this->render('update', compact('usersForm'));
    }
}
