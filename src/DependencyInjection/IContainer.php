<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\DependencyInjection;


use Atom\Uploader\Handler\UploadHandler;
use Atom\Uploader\Storage\StorageFactory;

interface IContainer
{
    /**
     * @return UploadHandler
     */
    public function getUploadHandler();

    /**
     * @return StorageFactory
     */
    public function getStorageFactory();
}