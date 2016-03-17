<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->fixers([
        '-empty_return',
        '-phpdoc_no_empty_return',
        '-spaces_cast',
        'short_array_syntax',
        'ordered_use',
    ])
    ->finder(Symfony\CS\Finder\DefaultFinder::create()->in([
        __DIR__ . '/src',
        __DIR__ . '/features/Context',
        __DIR__ . '/example-app/src',
    ]));
