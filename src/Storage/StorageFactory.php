<?php

namespace Atom\Uploader\Storage;


use Atom\Uploader\Exception\NoSuchStorageException;

class StorageFactory
{
    private $storageMap;

    public function __construct()
    {
        $this->storageMap = [];
    }

    public function addStorage($key, IStorage $storage)
    {
        $this->storageMap[$key] = $storage;
    }

    /**
     * @param $key string
     *
     * @throws NoSuchStorageException
     * @return IStorage
     */
    public function getStorage($key)
    {
        if (!array_key_exists($key, $this->storageMap)) {
            throw new NoSuchStorageException(sprintf('The storage "%s" does not exist.', $key));
        }

        return $this->storageMap[$key];
    }
}