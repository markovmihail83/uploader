<?php

/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

$cwd = getcwd();
$configDir = __DIR__ . '/../src/Resources/config';


$doctrineBin = sprintf('"%s" %s/../../vendor/doctrine/orm/bin/doctrine.php', PHP_BINARY, __DIR__);
$doctrineUpdateCommand = sprintf('%s orm:schema-tool:update --force', $doctrineBin);

//$odmBin = sprintf('"%s" %s/../../vendor/doctrine/orm/bin/doctrine.php', PHP_BINARY, __DIR__);
//$odmUpdateCommand = sprintf('%s doctrine:mongodbdb:schema:update --force', $odmBin);

chdir($configDir . '/orm');
exec($doctrineUpdateCommand);
chdir($configDir . '/orm_embeddable');
exec($doctrineUpdateCommand);

//chdir($configDir . '/odm');
//exec($odmUpdateCommand);

chdir($cwd);

$conn = require __DIR__ . '/../src/Resources/config/dbal/bootstrap.php';

$createScheme = 'CREATE TABLE IF NOT EXISTS uploadable (
  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  file VARCHAR(255)
)';

$conn->exec($createScheme);
