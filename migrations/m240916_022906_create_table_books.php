<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m240916_022906_create_table_books
 */
class m240916_022906_create_table_books extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('books', [
            'id' => Schema::TYPE_PK,
            'isbn' => Schema::TYPE_STRING . ' NOT NULL',
            'titulo' => Schema::TYPE_STRING . ' NOT NULL',
            'autor' => Schema::TYPE_STRING . ' NOT NULL',
            'preco' => Schema::TYPE_INTEGER . ' NOT NULL',
            'estoque' => Schema::TYPE_INTEGER . ' NOT NULL',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240916_022906_create_table_books cannot be reverted.\n";

        return false;
    }

    public function down()
    {
        $this->dropTable('books');

        echo "m240916_022906_create_table_books cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }
    */
}
