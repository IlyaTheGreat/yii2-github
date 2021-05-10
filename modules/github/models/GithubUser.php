<?php


namespace app\modules\github\models;


use yii\db\ActiveRecord;

/**
 * This is the model class for table "github_user".
 *
 * @property int $id
 * @property string $name
 */
class GithubUser extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'github_user';
    }

    /**
     * @return ActiveRecord[]
     */
    public static function getAll(): array
    {
        return GithubUser::find()->all();
    }

    /**
     * Get all names
     *
     * @return string[]
     */
    public static function getAllUsernames(): array
    {
        $models = self::getAll();
        $usernames = [];

        foreach ($models as $model) {
            $usernames[] = $model->name;
        }

        return $usernames;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Имя пользователя',
        ];
    }
}
