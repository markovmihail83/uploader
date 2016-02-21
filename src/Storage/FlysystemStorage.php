<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Storage;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;

class FlysystemStorage implements IStorage
{
    private $manager;

    public function __construct(MountManager $manager)
    {
        $this->manager = $manager;
    }

    public function writeStream($prefix, $path, $resource)
    {
        return $this->manager->getFilesystem($prefix)->writeStream($path, $resource);
    }

    public function delete($prefix, $path)
    {
        $fs = $this->manager->getFilesystem($prefix);

        if (empty($path) || !$fs->get($path)->isFile()) {
            return false;
        }

        return $fs->delete($path);
    }

    public function resolveFileInfo($prefix, $path)
    {
        $fs = $this->manager->getFilesystem($prefix);

        if (empty($path) || !$fs->get($path)->isFile()) {
            return null;
        }

        $this->tryRegisterFilesystemWrapper($prefix, $fs);

        return new \SplFileInfo(sprintf('%s://%s', $prefix, $path));
    }

    private function tryRegisterFilesystemWrapper($prefix, FilesystemInterface $fs)
    {
        if (in_array($prefix, stream_get_wrappers())) {
            return;
        }

        if (class_exists('Twistor\FlysystemStreamWrapper')) {
            \Twistor\FlysystemStreamWrapper::register($prefix, $fs);
        }
    }
}