<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\LazyLoad;

use Atom\Uploader\Handler\UploadHandler;

interface IUploadHandlerLazyLoader
{
    /**
     * @return UploadHandler
     */
    public function getUploadHandler();
}
