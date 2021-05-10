<?php


namespace app\modules\github\commands;


use app\modules\github\components\GithubCache;
use app\modules\github\components\GithubService;
use Exception;
use yii\console\Controller;
use yii\console\ExitCode;

class GithubController extends Controller
{
    /**
     * Updates github cache with displaying repositories
     *
     * @throws Exception if can't save cache
     */
    public function actionUpdate(): int
    {
        try {
            GithubCache::flush();

            $service = GithubService::getInstance();
            $service->saveCache();
        } catch (Exception $e) {
            throw new Exception();
        }

        return ExitCode::OK;
    }
}