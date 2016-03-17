<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\LazyLoad;

use Atom\Uploader\Filesystem\FilesystemAdapterRepo;

interface IFilesystemAdapterRepoLazyLoader
{
    /**
     * @return FilesystemAdapterRepo
     */
    public function getFilesystemAdapterRepo();
}
