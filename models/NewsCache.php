<?php
namespace app\models;

use yii\db\ActiveRecord;

class NewsCache extends ActiveRecord
{
    public static function tableName()
    {
        return 'news_cache';
    }

    public static function findByKey($key)
    {
        return static::findOne(['cache_key' => $key]);
    }

    public static function set($key, $response)
    {
        $model = static::findByKey($key) ?? new static();
        $model->cache_key = $key;
        $model->response = $response;
        $model->created_at = new \yii\db\Expression('now()');
        $model->save();
    }
}