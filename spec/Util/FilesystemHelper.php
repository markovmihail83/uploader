<?php

namespace spec\Atom\Uploader\Util;


use VirtualFileSystem\FileSystem;

trait FilesystemHelper
{
    private static $_virtualFilesystem;

    private static function virtualPath($path)
    {
        if (null === static::$_virtualFilesystem) {
            self::$_virtualFilesystem = new FileSystem();
        }

        $location = self::$_virtualFilesystem->path($path);

        return self::normalizePath($location);
    }

    private static function createVirtualFile($path, $contents = '')
    {
        $path = self::virtualPath($path);
        self::createVirtualFile($path, $contents);

        return $path;
    }

    public static function createFile($path, $contents = '', $directoryPerm = 0777) {
        @mkdir(dirname($path), $directoryPerm, true);
        file_put_contents($path, $contents);
    }

    public static function normalizePath($path)
    {
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

        return str_replace(':\\\\', '://', $path);
    }

    public static function joinPath()
    {
        $path = array_reduce(func_get_args(), function ($carry, $item) {
            if (null === $carry) {
                return rtrim($item, '\\/');
            }

            return $carry . '/' . trim($item, '\\/');
        });

        return self::normalizePath($path);
    }
}