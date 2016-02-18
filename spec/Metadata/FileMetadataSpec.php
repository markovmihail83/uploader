<?php


namespace spec\Atom\Uploader\Metadata;

use Atom\Uploader\Metadata\FileMetadata;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileMetadataSpec extends ObjectBehavior
{
    const FILE_WRITE_PROP = 'file';
    const FILE_READ_PROP = 'file';
    const URI_WRITE_PROP = 'uri';
    const FILE_INFO_WRITE_PROP = 'fileInfo';
    const FS_PREFIX = 'fs_prefix';
    const URI_PREFIX = '/uploads';
    const STORAGE_TYPE = 'my_storage';
    const NAMING_STRATEGY = 'my_namer';
    const DELETE_ON_UPDATE = false;
    const DELETE_ON_REMOVE = true;
    const INJECT_URI_ON_LOAD = true;
    const INJECT_FILE_INFO_ON_LOAD = false;


    function let()
    {
        $this->beConstructedWith(
            self::FILE_WRITE_PROP,
            self::FILE_WRITE_PROP,
            self::URI_WRITE_PROP,
            self::FILE_INFO_WRITE_PROP,
            self::FS_PREFIX,
            self::URI_PREFIX,
            self::STORAGE_TYPE,
            self::NAMING_STRATEGY,
            self::DELETE_ON_UPDATE,
            self::DELETE_ON_REMOVE,
            self::INJECT_URI_ON_LOAD,
            self::INJECT_FILE_INFO_ON_LOAD
        );
    }

    function it_should_get_a_file_info_setter()
    {
        $this->getFileInfoSetter()->shouldBe(self::FILE_INFO_WRITE_PROP);
    }

    function it_should_get_a_file_setter()
    {
        $this->getFileSetter()->shouldBe(self::FILE_WRITE_PROP);
    }

    function it_should_get_a_file_getter()
    {
        $this->getFileGetter()->shouldBe(self::FILE_READ_PROP);
    }

    function it_should_get_an_uri_setter()
    {
        $this->getUriSetter()->shouldBe(self::URI_WRITE_PROP);
    }

    function it_should_get_a_fs_prefix_prop()
    {
        $this->getFilesystemPrefix()->shouldBe(self::FS_PREFIX);
    }

    function it_should_get_an_uri_format()
    {
        $this->getUriPrefix()->shouldBe(self::URI_PREFIX);
    }

    function it_should_get_a_storage_type()
    {
        $this->getStorageType()->shouldBe(self::STORAGE_TYPE);
    }

    function it_should_get_a_naming_strategy()
    {
        $this->getNamingStrategy()->shouldBe(self::NAMING_STRATEGY);
    }

    function it_should_check_injectable_uri() {
        $this->isInjectableUri()->shouldBe(self::INJECT_URI_ON_LOAD);
    }

    function it_should_check_injectable_file_info() {
        $this->isInjectableFileInfo()->shouldBe(self::INJECT_FILE_INFO_ON_LOAD);
    }

    function it_should_check_deletable()
    {
        $this->isDeletable(true)->shouldBe(self::DELETE_ON_UPDATE);
        $this->isDeletable(false)->shouldBe(self::DELETE_ON_REMOVE);
    }
}