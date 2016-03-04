<?php

/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

$config = \Doctrine\ORM\Tools\Setup::createXMLMetadataConfiguration([__DIR__ . '/mappings'], true);

$conn = [
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/../../data/orm.sqlite',
];

return \Doctrine\ORM\EntityManager::create($conn, $config);