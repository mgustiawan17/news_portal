<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName() 
    { 
        return 'app_user'; 
    }

    public static function findIdentity($id) 
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null) 
    {
        return null;
    }

    public static function findByEmail($email) 
    {
        return static::findOne(['email' => $email]);
    }

    public function getId() 
    { 
        return $this->id; 
    }

    public function getAuthKey() 
    { 
        return null; 
    }

    public function validateAuthKey($authKey) 
    { 
        return false; 
    }

    public function validatePassword($password) 
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password) 
    {
        $this->password_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
    }
}
