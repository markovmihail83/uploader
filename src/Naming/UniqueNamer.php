<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Naming;

class UniqueNamer implements INamer
{
    use EscapeFilename;

    /**
     * @param \SplFileInfo $file
     *
     * @return string
     */
    public function name(\SplFileInfo $file)
    {
        $name = uniqid(null, true);
        $extension = $this->escape($file->getExtension());

        if (!empty($extension)) {
            $name = sprintf('%s.%s', $name, $extension);
        }

        return $name;
    }
}
