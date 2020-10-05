<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=office',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'attributes' => [
        PDO::ATTR_CASE => PDO::CASE_LOWER,
    ]
];
