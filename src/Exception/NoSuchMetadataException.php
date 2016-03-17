<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Exception;

use Exception;

class NoSuchMetadataException extends \RuntimeException
{
    public function __construct($className, $code = null, Exception $previous = null)
    {
        parent::__construct(sprintf('No metadata for "%s".', $className), $code, $previous);
    }
}
