<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m240913_210712_create_table_users
 */
class m240913_210712_create_table_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('users', [
            'id' => Schema::TYPE_PK,
            'nome' => Schema::TYPE_STRING . ' NOT NULL',
            'senha' => Schema::TYPE_STRING . ' NOT NULL',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240913_210712_create_table_users cannot be reverted.\n";

        return false;
    }

    public function down()
    {
        $this->dropTable('users');

        echo "m240913_210712_create_table_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }
    */
}
