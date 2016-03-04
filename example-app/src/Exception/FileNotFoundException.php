<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Exception;


use Exception;

class FileNotFoundException extends \RuntimeException
{
    public function __construct($path, $code = 0, Exception $previous = null)
    {
        parent::__construct(sprintf('File not found at path: %s', $path), $code, $previous);
    }
}