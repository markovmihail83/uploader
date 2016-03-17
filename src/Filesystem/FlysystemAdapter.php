<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Filesystem;

use Atom\Uploader\ThirdParty\FlysystemStreamWrapper;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;

/**
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class FlysystemAdapter implements IFilesystemAdapter
{
    private $manager;

    private $wrapper;

    public function __construct(MountManager $manager, FlysystemStreamWrapper $wrapper)
    {
        $this->manager = $manager;
        $this->wrapper = $wrapper;
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
        if ($this->wrapper->isExist()) {
            $this->wrapper->register($prefix, $fs);
        }
    }
}
