<?php

use yii\db\Migration;

class m251017_141617_create_rating_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('rating', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'article_url' => $this->string(2048)->notNull(),
            'vote' => $this->smallInteger()->notNull(), // 1 or -1
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->null(),
        ]);
        $this->createIndex('idx-rating-user_article', 'rating', ['user_id','article_url'], true);
        $this->createIndex('idx-rating-article', 'rating', 'article_url');
        $this->addForeignKey('fk-rating-user', 'rating', 'user_id', 'app_user', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-rating-user', 'rating');
        $this->dropTable('rating');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_141617_create_rating_tables cannot be reverted.\n";

        return false;
    }
    */
}
