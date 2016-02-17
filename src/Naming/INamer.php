<?php

namespace Atom\Uploader\Naming;


interface INamer
{
    /**
     * @param \SplFileInfo $file
     *
     * @return string
     */
    public function name(\SplFileInfo $file);
}