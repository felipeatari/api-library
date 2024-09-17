<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m240917_032013_create_table_book_covers
 */
class m240917_032013_create_table_book_covers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('book_covers', [
            'id' => Schema::TYPE_PK,
            'book_id' => Schema::TYPE_STRING . ' NOT NULL',
            'cover' => Schema::TYPE_STRING . ' NOT NULL',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240917_032013_create_table_book_covers cannot be reverted.\n";

        return false;
    }

    public function down()
    {
        $this->dropTable('book_covers');

        echo "m240917_032013_create_table_book_covers cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }
    */
}
