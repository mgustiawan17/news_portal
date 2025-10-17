<?php
namespace app\models;

use yii\db\ActiveRecord;

class Rating extends ActiveRecord
{
    public static function tableName()
    {
        return 'rating';
    }

    public function rules()
    {
        return [
            [['user_id', 'article_url', 'vote'], 'required'],
            [['user_id', 'vote'], 'integer'],
            [['vote'], 'in', 'range' => [1, -1]],
            [['article_url'], 'string', 'max' => 2048],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        $this->updated_at = date('Y-m-d H:i:s');
        return true;
    }
}
