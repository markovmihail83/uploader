<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Naming;

class BasenameNamer implements INamer
{
    use EscapeFilename;

    /**
     * @param \SplFileInfo $file
     *
     * @return string
     */
    public function name(\SplFileInfo $file)
    {
        return $this->escape($file->getFilename());
    }
}
