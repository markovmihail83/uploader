<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Filesystem;

interface IFilesystemAdapter
{
    /**
     * @param string $prefix
     * @param string $path
     * @param resource $resource
     *
     * @throws \Exception
     */
    public function writeStream($prefix, $path, $resource);

    /**
     * @param string $prefix
     * @param string $path
     *
     * @return bool
     */
    public function delete($prefix, $path);

    /**
     * @param string $prefix
     * @param string $path
     *
     * @return \SplFileInfo|null
     */
    public function resolveFileInfo($prefix, $path);
}
