<?php
namespace app\models;

use yii\db\ActiveRecord;

class Bookmark extends ActiveRecord
{
    public static function tableName()
    {
        return 'bookmark';
    }

    public function rules()
    {
        return [
            [['user_id', 'article_url'], 'required'],
            [['user_id'], 'integer'],
            [['article_data'], 'safe'],
            [['article_url'], 'string', 'max' => 2048],
            [['article_title'], 'string', 'max' => 1024],
            [['article_source'], 'string', 'max' => 255],
        ];
    }
}
