<?php

namespace Atom\Uploader\Naming;


class UniqueNamer implements INamer
{

    /**
     * @param \SplFileInfo $file
     *
     * @return string
     */
    public function name(\SplFileInfo $file) {
        $name = uniqid(null, true);

        $extension = $file->getExtension();

        if ($extension) {
            $name = sprintf('%s.%s', $name, $extension);
        }

        return $name;
    }
}