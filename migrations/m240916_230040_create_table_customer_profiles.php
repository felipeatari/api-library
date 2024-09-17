<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m240916_230040_create_table_customer_profiles
 */
class m240916_230040_create_table_customer_profiles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('customer_profiles', [
            'id' => Schema::TYPE_PK,
            'customer_id' => Schema::TYPE_STRING . ' NOT NULL',
            'profile' => Schema::TYPE_STRING . ' NOT NULL',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240916_230040_create_table_customer_profiles cannot be reverted.\n";

        return false;
    }

    public function down()
    {
        $this->dropTable('customer_profiles');

        echo "m240916_230040_create_table_customer_profiles cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }
    */
}
