<?php

namespace spec\Atom\Uploader\Util;


use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

trait FilesystemHelper
{

    public function mount()
    {
        if (!in_array('vfs', stream_get_wrappers())) {
            vfsStreamWrapper::register();
        }
    }

    public function unMount()
    {
        if (in_array('vfs', stream_get_wrappers())) {
            vfsStreamWrapper::unregister();
        }
    }

    public function __construct()
    {
        $this->mount();
    }

    public function __destruct()
    {
        $this->unMount();
    }

    private static function createVirtualFile($path, $contents = '')
    {
        $path = vfsStream::url($path);
        self::createVirtualFile($path, $contents);

        return $path;
    }

    public static function createFile($path, $contents = '', $directoryPerm = 0777)
    {
        @mkdir(dirname($path), $directoryPerm, true);
        file_put_contents($path, $contents);
    }

    public static function joinPath()
    {
        $path = array_reduce(func_get_args(), function ($carry, $item) {
            if (null === $carry) {
                return rtrim($item, '\\/');
            }

            return $carry . '/' . trim($item, '\\/');
        });

        return $path;
    }
}