<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Storage;

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

        return new \SplFileInfo($fs->getMetadata($path)['path']);
    }
}