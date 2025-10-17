<?php
namespace app\models;
use yii\db\ActiveRecord;

class LoginAttempt extends ActiveRecord 
{
    public static function tableName()
    { 
        return 'login_attempt'; 
    }
}
