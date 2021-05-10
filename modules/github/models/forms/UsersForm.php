<?php


namespace app\modules\github\models\forms;


use yii\base\Model;

class UsersForm extends Model
{
    /**
     * @var string Список пользователей github
     */
    public string $users;

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['users'], 'required'],
            [['users'], 'string'],
        ];
    }
}