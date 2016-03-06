<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Storage;


use Atom\Uploader\Exception\NoSuchStorageException;

class StorageFactory
{
    private $storageMap;

    /**
     * @param IStorage[] $storageMap
     */
    public function __construct(array $storageMap)
    {
        $this->storageMap = $storageMap;
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