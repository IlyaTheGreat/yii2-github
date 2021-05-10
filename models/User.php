<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer    $id
 * @property string     $username
 * @property string     $password_hash
 * @property string     $password_reset_token
 * @property string     $email
 * @property string     $auth_key
 * @property integer    $status
 * @property integer    $created_at
 * @property integer    $updated_at
 * @property string     $password write-only password
 * @property string     $access_token
 * @property integer    $token_expiry
 *
 * @property string     $newPassword
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_BLOCKED = 0;
    const STATUS_ACTIVE = 10;

    const ROLE_USER = 'user';
    const ROLE_MANAGER = 'manager';
    const ROLE_ADMIN = 'admin';

    const TOKEN_EXPIRY = 86400 * 10; // 10 days

    /**
     * @var string
     */
    public string $newPassword = '';

    /**
     * Получение списка ролей
     *
     * @param   bool    $withDescription
     *
     * @return  array   User roles
     */
    public static function roles($withDescription = false): array
    {
        return $withDescription ? [
            self::ROLE_USER => 'Пользователь',
            self::ROLE_MANAGER => 'Менеджер',
            self::ROLE_ADMIN => 'Администратор',
        ] : [
            self::ROLE_USER => Yii::t('app', 'User'),
            self::ROLE_MANAGER => Yii::t('app', 'Manager'),
            self::ROLE_ADMIN => Yii::t('app', 'Admin'),
        ];
    }

    /**
     * Получение списка статусов
     *
     * @param   bool    $withDescription
     *
     * @return  array   User roles
     */
    public static function statuses($withDescription = false): array
    {
        return $withDescription ? [
            self::STATUS_BLOCKED => 'Заблокирован',
            self::STATUS_ACTIVE => 'Активен',
        ] : [
            self::STATUS_BLOCKED => Yii::t('app', 'Blocked'),
            self::STATUS_ACTIVE => Yii::t('app', 'Active'),
        ];
    }

    /**
     * Получение названия роли пользователя
     *
     * @return string|null
     */
    public function getRoleName(): ?string
    {
        $list = self::roles(true);
        return $list[$this->identity->role] ?? null;
    }

    /**
     * Получение названия статуса пользователя
     *
     * @return mixed|null
     */
    public function getStatusName(): ?string
    {
        $list = self::statuses(true);
        return $list[$this->status] ?? null;
    }

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_BLOCKED]],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'username' => 'Имя пользователя',
            'email' => 'E-mail',
            'status' => 'Статус',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
            'password' => 'Пароль',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     *
     * @return static|null
     */
    public static function findByUsername(string $username): ?User
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     *
     * @throws Yii\base\Exception
     */
    public function setPassword(string $password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     *
     * @throws yii\base\Exception
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * @param $token
     *
     * @return User|null
     */
    public static function findByPasswordResetToken($token): ?User
    {
        if (!static::isPasswordResetTokenValid($token))
            return null;

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * @param $token
     *
     * @return bool
     */
    public static function isPasswordResetTokenValid($token): bool
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @throws yii\base\Exception
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * set password_reset_token null value
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->access_token;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave($insert): bool
    {
        if (parent::beforeSave($insert)) {
            $time = time();
            $this->access_token = Yii::$app->security->generateRandomString(25);
            $this->token_expiry = $time + self::TOKEN_EXPIRY;

            if ($this->isNewRecord) {
                $this->created_at = $time;
            }

            $this->updated_at = $time;

            return true;
        }

        return false;
    }

    /**
     * Проверка токена
     *
     * @param  string   $accessToken
     *
     * @return bool
     */
    public function validateAccessToken(string $accessToken): bool
    {
        return $this->getAccessToken() === $accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user = static::findOne(['access_token' => $token]);

        if ($user && $user->token_expiry > time()) {
            $user->access_token = Yii::$app->security->generateRandomString(25);
            $user->token_expiry = time() + self::TOKEN_EXPIRY;
            return $user;
        }

        return null;
    }

    /**
     * Проверяет соответствие переданного токена переданному id
     *
     * @param  $id
     * @param  $token
     *
     * @return bool
     */
    public static function validateTokenById($id, $token): bool
    {
        return !empty(self::findOne(['id' => $id, 'access_token' => $token]));
    }
}