<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Exception;

use Exception;

class NoSuchFilesystemException extends \RuntimeException
{
    public function __construct($filesystemName, $code = null, Exception $previous = null)
    {
        parent::__construct(sprintf('The filesystem "%s" does not exist.', $filesystemName), $code, $previous);
    }
}
