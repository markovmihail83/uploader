<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\LazyLoad;


use Atom\Uploader\Storage\StorageFactory;

interface IStorageFactoryLazyLoader
{
    /**
     * @return StorageFactory
     */
    public function getStorageFactory();
}