<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace spec\Atom\Uploader\Util;

use org\bovigo\vfs\vfsStreamWrapper;

trait FilesystemHelper
{
    public static function createFile($path, $contents = '', $directoryPerm = 0777)
    {
        @mkdir(dirname($path), $directoryPerm, true);
        file_put_contents($path, $contents);
    }

    public static function joinPath()
    {
        $path = array_reduce(
            func_get_args(),
            function ($carry, $item) {
                if (null === $carry) {
                    return rtrim($item, '\\/');
                }

                return $carry . '/' . trim($item, '\\/');
            }
        );

        return $path;
    }

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
}
