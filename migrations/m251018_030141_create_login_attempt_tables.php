<?php

use yii\db\Migration;

class m251018_030141_create_login_attempt_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('login_attempt', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'email_attempt' => $this->text(),
            'success' => $this->boolean()->defaultValue(false),
            'ip_addr' => $this->string(50),
            'user_agent' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Foreign key ke app_user
        $this->addForeignKey(
            'fk_login_attempt_user',
            'login_attempt',
            'user_id',
            'app_user',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_login_attempt_user', 'login_attempt');
        $this->dropTable('login_attempt');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251018_030141_create_login_attempt_tables cannot be reverted.\n";

        return false;
    }
    */
}
