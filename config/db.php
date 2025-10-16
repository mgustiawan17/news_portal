<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => getenv('DB_DSN') ?: 'pgsql:host=127.0.0.1;port=5432;dbname=news_portal',
    'username' => getenv('DB_USER') ?: 'postgres',
    'password' => getenv('DB_PASS') ?: '',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
