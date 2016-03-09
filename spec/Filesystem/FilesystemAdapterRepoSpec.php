<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace spec\Atom\Uploader\Filesystem;

use Atom\Uploader\Exception\NoSuchFilesystemException;
use Atom\Uploader\Filesystem\IFilesystemAdapter;
use Atom\Uploader\Filesystem\FilesystemAdapterRepo;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin FilesystemAdapterRepo
 */
class FilesystemAdapterRepoSpec extends ObjectBehavior
{
    function let(IFilesystemAdapter $filesystem)
    {
        $this->beConstructedWith(['my_filesystem' => $filesystem]);
    }

    function it_should_get_filesystem($filesystem)
    {
        $this->getFilesystem('my_filesystem')->shouldBe($filesystem);
    }

    function it_should_throw_exception_when_getting_a_filesystem()
    {
        $this->shouldThrow(NoSuchFilesystemException::class)->duringGetFilesystem('non_existent_filesystem');
    }
}