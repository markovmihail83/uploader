<?php
/**
 * Copyright © 2017 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

$config = new \Doctrine\ODM\MongoDB\Configuration();
$driver = new \Doctrine\ODM\MongoDB\Mapping\Driver\YamlDriver([__DIR__ . '/mappings']);
$config->setMetadataDriverImpl($driver);

$conn = new \Doctrine\MongoDB\Connection('127.0.0.1');

return \Doctrine\ODM\MongoDB\DocumentManager::create($conn, $config);