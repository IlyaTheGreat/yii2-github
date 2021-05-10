<?php


namespace app\modules\github\components;


use Yii;

abstract class GithubCache
{
    /**
     * Время кеширования в секундах
     */
    const CACHE_TIME = 600; //10 минут
    const GLOBAL_KEY = 'github';
    const RESULT_KEY = 'result';
    const UPDATE_KEY = 'updated_at';

    /**
     * Сохранение репозиториев в кэше
     *
     * @param mixed $result
     *
     * @return bool
     */
    public static function save($result): bool
    {
        return Yii::$app->cache->set(
            self::GLOBAL_KEY,
            [
                self::RESULT_KEY => $result,
                self::UPDATE_KEY => date('Y-m-d H:i:s', time()),
            ],
            self::CACHE_TIME
        );
    }

    /**
     * Возвращает последние репозитории
     *
     * @return mixed
     */
    public static function load()
    {
        return Yii::$app->cache->get(self::GLOBAL_KEY);
    }

    /**
     * Check if cache is empty
     *
     * @return bool
     */
    public static function isEmpty(): bool
    {
        return empty(self::load());
    }

    /**
     * Очистка кэша
     *
     * @return void
     */
    public static function flush(): void
    {
        !self::isEmpty() && Yii::$app->cache->delete(self::GLOBAL_KEY);
    }
}