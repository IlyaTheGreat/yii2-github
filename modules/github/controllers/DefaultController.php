<?php


namespace app\modules\github\controllers;


use app\modules\github\components\GithubService;
use yii\web\Controller;

class DefaultController extends Controller
{
    /**
     * Show last repositories links
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $service = GithubService::getInstance();

        return $this->render('index', [
            'data' => $service->getLastRepositories(),
        ]);
    }
}
