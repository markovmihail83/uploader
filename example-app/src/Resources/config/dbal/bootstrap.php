<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

require_once __DIR__ . '/../../../../../vendor/autoload.php';

$config = [
    'user' => 'example-app',
    'password' => 'example-app',
    'driver' => 'pdo_sqlite',
    'path' => sprintf('%s/dbal.sqlite', realpath(__DIR__ . '/../../data')),
];

$conn = \Doctrine\DBAL\DriverManager::getConnection($config);
$conn->connect();

return $conn;