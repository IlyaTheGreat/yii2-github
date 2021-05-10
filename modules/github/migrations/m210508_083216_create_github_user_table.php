<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%github_user}}`.
 */
class m210508_083216_create_github_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%github_user}}', [
            'id'    => $this->primaryKey(),
            'name'  => $this->string()->unique()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%github_user}}');
    }
}
