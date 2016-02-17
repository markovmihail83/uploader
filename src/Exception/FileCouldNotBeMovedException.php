<?php


namespace Atom\Uploader\Exception;


use Exception;

class FileCouldNotBeMovedException extends \RuntimeException
{
    public function __construct($from, $to, $code = 0, Exception $previous = null) {
        parent::__construct(sprintf('"%s" could not be moved to "%s"', $from, $to), $code, $previous);
    }
}