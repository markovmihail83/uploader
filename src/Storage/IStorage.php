<?php

namespace Atom\Uploader\Storage;

interface IStorage
{
    /**
     * @param string   $prefix
     * @param string   $path
     * @param resource $resource
     *
     * @throws \Exception
     *
     * @return bool
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