<?php


namespace spec\Atom\Uploader\Storage;

use Atom\Uploader\Storage\FlysystemStorage;
use League\Flysystem\Filesystem;
use League\Flysystem\Handler;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin FlysystemStorage
 */
class FlysystemStorageSpec extends ObjectBehavior
{
    const FS_PREFIX = 'fs_prefix';
    const PATH = 'path/to/file';

    function let(MountManager $manager, Filesystem $fs, Handler $handler)
    {
        $this->beConstructedWith($manager);
        $manager->getFilesystem(self::FS_PREFIX)->willReturn($fs);
        $fs->get(Argument::type('string'))->willReturn($handler);
        $handler->isFile()->willReturn(true);
    }

    function it_should_write_stream($fs)
    {
        $stream = tmpfile();
        $fs->writeStream('path/to/file', $stream)->willReturn(true)->shouldBeCalled();
        $this->writeStream(self::FS_PREFIX, 'path/to/file', $stream)->shouldBe(true);
    }

    function it_should_delete_file($fs)
    {
        $fs->delete(self::PATH)->willReturn(true)->shouldBeCalled();
        $this->delete(self::FS_PREFIX, self::PATH)->shouldBe(true);
    }

    function it_should_not_delete_file_when_path_is_not_file($handler)
    {
        $handler->isFile()->willReturn(false);
        $this->delete(self::FS_PREFIX, self::PATH)->shouldBe(false);
    }

    function it_should_not_delete_file_when_path_is_empty()
    {
        $this->delete(self::FS_PREFIX, '')->shouldBe(false);
    }

    function it_should_resolve_file_info($fs)
    {
        $fs->getMetadata(self::PATH)->willReturn(['path' => self::PATH]);
        $this->resolveFileInfo(self::FS_PREFIX, self::PATH)->shouldBeAnInstanceOf(\SplFileInfo::class);
    }

    function it_should_not_resolve_file_info_when_path_is_empty()
    {
        $this->resolveFileInfo(self::FS_PREFIX, '')->shouldBe(null);
    }

    function it_should_not_resolve_file_info_when_path_is_not_file($handler)
    {
        $handler->isFile()->willReturn(false);
        $this->resolveFileInfo(self::FS_PREFIX, self::PATH)->shouldBe(null);
    }
}