<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Exception;


use Exception;

class ObjectNotFoundException extends \Exception
{
    public function __construct($id, $driver, $code = null, Exception $previous = null)
    {
        $message = sprintf('Object with id "%s" is not found on database layer "%s".', $id, $driver);

        parent::__construct($message, $code, $previous);
    }
}