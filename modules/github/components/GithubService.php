<?php


namespace app\modules\github\components;


use app\modules\github\models\GithubUser;
use Exception;
use Github\Client;
use Yii;

final class GithubService
{
    /**
     * Count of repositories to show
     */
    const REPOSITORIES_COUNT = 10;
    const CONDITION = 'pushed_at'; //'updated_at'

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var array
     */
    private static array $instances = [];

    /**
     * GithubService constructor.
     */
    protected function __construct()
    {
        $this->client = new Client();
    }

    /**
     * GithubService clone.
     * Deny clone
     */
    protected function __clone() { }

    /**
     * GithubService wakeup.
     * Deny wakeup
     *
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a GithubService.");
    }

    /**
     * gets the instance via lazy initialization (created on first usage)
     * Controlling access to an instance of a class
     *
     * @return GithubService
     */
    public static function getInstance(): GithubService
    {
        $cls = self::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new self();
        }

        return self::$instances[$cls];
    }

    /**
     * Get all users with their public repositories,
     * if user not exist array with 'username' key will be empty
     *
     * @return array    ['username' => $repositories[]]
     */
    private function getAllUsersWithRepositories(): array
    {
        $usersWithRepositories = [];
        $users = GithubUser::getAll();

        foreach ($users as $user) {
            try {
                $userRepositories = $this->client
                    ->api('users')
                    ->repositories($user->name, 'owner', self::CONDITION, 'desc');

                if (!empty($userRepositories)) {
                    $usersWithRepositories[$user->name] = $userRepositories;
                }
            } catch (Exception $e) {
                Yii::error($e->getMessage(), 'github');
                continue;
            }
        }

        return $usersWithRepositories;
    }

    /**
     * Filter last self::REPOSITORIES_COUNT users which update their $repositories
     *
     * @param  array  $usersWithRepositories ['username' => $repositories[]]
     *
     * @return array                         ['username' => $repositories[]]
     */
    private function selectLastUsers(array $usersWithRepositories): array
    {
        if (count($usersWithRepositories) > self::REPOSITORIES_COUNT) {
            usort($usersWithRepositories, function($a, $b) {
                return strtotime($a[0][self::CONDITION]) < strtotime($b[0][self::CONDITION]);
            });
            while(count($usersWithRepositories) > self::REPOSITORIES_COUNT) {
                array_pop($usersWithRepositories);
            }
        }

        return $usersWithRepositories;
    }

    /**
     * Filter last self::REPOSITORIES_COUNT updated $repositories
     *
     * @param $lastRepositories ['name' => 'repository name', 'link' => 'absolute url']
     *
     * @return array            ['name' => 'repository name', 'link' => 'absolute url']
     */
    private function selectLastRepositories(array $lastRepositories): array
    {
        $result = [];

        for ($i = 0; $i < count($lastRepositories) && $i < self::REPOSITORIES_COUNT; $i++) {
            $result[$i] = [
                'name' => $lastRepositories[$i]['name'],
                'link' => $lastRepositories[$i]['html_url'],
            ];
        }

        return $result;
    }

    /**
     * Load repositories list form users regardless of them
     *
     * @param  array $usersWithRepositories
     *
     * @return array repository list
     */
    private function createLastUsersRepositoriesList(array $usersWithRepositories): array
    {
        $lastRepositories = [];

        foreach($usersWithRepositories as $list) {
            for ($i = 0; $i < count($list) && $i < self::REPOSITORIES_COUNT; $i++) {
                $lastRepositories[] = $list[$i];
            }
        }

        usort($lastRepositories, function($a, $b) {
            return strtotime($a[self::CONDITION]) < strtotime($b[self::CONDITION]);
        });

        return $lastRepositories;
    }

    /**
     * Save repositories in cache
     *
     * @return void
     */
    public function saveCache(): void
    {
        $usersWithRepositories = $this->selectLastUsers(
            $this->getAllUsersWithRepositories()
        );
        $lastUsersWithRepositories = $this->createLastUsersRepositoriesList($usersWithRepositories);
        $lastRepositories = $this->selectLastRepositories($lastUsersWithRepositories);

        GithubCache::save($lastRepositories);
    }

    /**
     * Get last self::REPOSITORIES_COUNT repositories from all github_users
     *
     * @return array
     * * [
     * *     'result'     => ['name' => 'repository name', 'link' => 'absolute url']
     * *     'updated_at' => 'Y-m-d H:i:s'
     * * ]
     */
    public function getLastRepositories(): array
    {
        if (GithubCache::isEmpty()) {
            $this->saveCache();
        }

        return GithubCache::load();
    }
}