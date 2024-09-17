<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m240914_202500_create_table_customers
 */
class m240914_202500_create_table_customers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('customers', [
            'id' => Schema::TYPE_PK,
            'nome' => Schema::TYPE_STRING . ' NOT NULL',
            'cpf' => Schema::TYPE_STRING . ' NOT NULL',
            'cep' => Schema::TYPE_STRING . ' NOT NULL',
            'logradouro' => Schema::TYPE_STRING . ' NOT NULL',
            'numero' => Schema::TYPE_STRING . ' NOT NULL',
            'cidade' => Schema::TYPE_STRING . ' NOT NULL',
            'estado' => Schema::TYPE_STRING . ' NOT NULL',
            'complemento' => Schema::TYPE_STRING . ' NOT NULL',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240914_202500_create_table_customers cannot be reverted.\n";

        return false;
    }

    public function down()
    {
        $this->dropTable('customers');

        echo "m240914_202500_create_table_customers cannot be reverted.\n";

        return false;
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }
    */
}
