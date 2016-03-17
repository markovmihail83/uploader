<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Exception;

use Exception;

/**
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class FileCouldNotBeMovedException extends \RuntimeException
{
    public function __construct($from, $to, $code = 0, Exception $previous = null)
    {
        parent::__construct(sprintf('"%s" could not be moved to "%s"', $from, $to), $code, $previous);
    }
}
