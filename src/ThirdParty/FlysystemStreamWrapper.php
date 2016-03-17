<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\ThirdParty;

use League\Flysystem\FilesystemInterface;

/**
 * @SuppressWarnings(PHPMD.StaticAccess, PHPMD.ShortVariable)
 */
class FlysystemStreamWrapper
{
    public function isExist()
    {
        return class_exists('\Twistor\FlysystemStreamWrapper');
    }

    public function register($protocol, FilesystemInterface $fs, array $configuration = null)
    {
        return \Twistor\FlysystemStreamWrapper::register($protocol, $fs, $configuration);
    }

    public function unRegister($protocol)
    {
        return \Twistor\FlysystemStreamWrapper::unregister($protocol);
    }
}
