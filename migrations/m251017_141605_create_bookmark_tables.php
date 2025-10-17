<?php

use yii\db\Migration;

class m251017_141605_create_bookmark_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('bookmark', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'article_url' => $this->string(2048)->notNull(),
            'article_title' => $this->string(1024)->null(),
            'article_source' => $this->string(255)->null(),
            'article_data' => $this->text()->null(), // serialized JSON
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
        $this->createIndex('idx-bookmark-user_article', 'bookmark', ['user_id','article_url'], true);
        // FK to app_user (table name might be 'app_user' as your logs show)
        $this->addForeignKey('fk-bookmark-user', 'bookmark', 'user_id', 'app_user', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-bookmark-user', 'bookmark');
        $this->dropTable('bookmark');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_141605_create_bookmark_tables cannot be reverted.\n";

        return false;
    }
    */
}
