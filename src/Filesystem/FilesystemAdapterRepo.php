<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Filesystem;

use Atom\Uploader\Exception\NoSuchFilesystemException;

class FilesystemAdapterRepo
{
    private $filesystemMap;

    /**
     * @param IFilesystemAdapter[] $filesystemMap
     */
    public function __construct(array $filesystemMap)
    {
        $this->filesystemMap = $filesystemMap;
    }

    /**
     * @param $filesystemName string
     *
     * @throws NoSuchFilesystemException
     *
     * @return IFilesystemAdapter
     */
    public function getFilesystem($filesystemName)
    {
        if (!array_key_exists($filesystemName, $this->filesystemMap)) {
            throw new NoSuchFilesystemException($filesystemName);
        }

        return $this->filesystemMap[$filesystemName];
    }
}
