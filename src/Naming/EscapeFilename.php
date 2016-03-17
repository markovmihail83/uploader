<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Naming;

trait EscapeFilename
{
    private function escape($string)
    {
        return preg_replace('/[^\w\d_\.-]/u', '_', (string)$string);
    }
}
