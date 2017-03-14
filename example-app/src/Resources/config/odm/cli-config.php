<?php

/**
 * Copyright © 2017 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

/**
 * @var \Composer\Autoload\ClassLoader
 */
require_once __DIR__ . '/../../../../../vendor/autoload.php';

$om = require_once __DIR__ . '/bootstrap.php';

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($om);
