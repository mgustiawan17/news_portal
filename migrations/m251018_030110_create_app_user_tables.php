<?php

use yii\db\Migration;

class m251018_030110_create_app_user_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('app_user', [
            'id' => $this->primaryKey(),
            'full_name' => $this->text()->notNull(),
            'email' => $this->text()->notNull(),
            'birth_year' => $this->integer()->notNull(),
            'password_hash' => $this->text()->notNull(),
            'failed_attempts' => $this->integer()->defaultValue(0),
            'last_failed_at' => $this->timestamp(),
            'blocked_until' => $this->timestamp(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'verification_token' => $this->string(255),
            'is_verified' => $this->smallInteger()->defaultValue(0),
        ]);

        $this->createIndex('idx_app_user_email_unique', 'app_user', 'email', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('app_user');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251018_030110_create_app_user_tables cannot be reverted.\n";

        return false;
    }
    */
}
