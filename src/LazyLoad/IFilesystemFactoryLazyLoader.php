<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\LazyLoad;


use Atom\Uploader\Filesystem\FilesystemFactory;

interface IFilesystemFactoryLazyLoader
{
    /**
     * @return FilesystemFactory
     */
    public function getFilesystemFactory();
}