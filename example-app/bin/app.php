<?php

/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

/**
 * @var \Composer\Autoload\ClassLoader $loader
 */
require_once __DIR__ . '/../../vendor/autoload.php';

$app = new \Symfony\Component\Console\Application();

\ExampleApp\Setup::setup($app);

$app->run();