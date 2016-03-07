<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace spec\Atom\Uploader\Metadata;

use Atom\Uploader\Metadata\FileMetadata;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin FileMetadata
 */
class FileMetadataSpec extends ObjectBehavior
{
    const FILE_SETTER = 'file';
    const FILE_GETTER = 'file';
    const URI_SETTER = 'uri';
    const FILE_INFO_SETTER = 'fileInfo';
    const FS_PREFIX = 'fs_prefix';
    const URI_PREFIX = '/uploads';
    const FS_ADAPTER = 'my_adapter';
    const NAMING_STRATEGY = 'my_namer';
    const DELETE_OLD_FILE = false;
    const DELETE_ON_REMOVE = true;
    const INJECT_URI_ON_LOAD = true;
    const INJECT_FILE_INFO_ON_LOAD = false;


    function let()
    {
        $this->beConstructedWith(
            self::FILE_SETTER,
            self::FILE_SETTER,
            self::URI_SETTER,
            self::FILE_INFO_SETTER,
            self::FS_PREFIX,
            self::URI_PREFIX,
            self::FS_ADAPTER,
            self::NAMING_STRATEGY,
            self::DELETE_OLD_FILE,
            self::DELETE_ON_REMOVE,
            self::INJECT_URI_ON_LOAD,
            self::INJECT_FILE_INFO_ON_LOAD
        );
    }

    function it_should_get_a_file_info_setter()
    {
        $this->getFileInfoSetter()->shouldBe(self::FILE_INFO_SETTER);
    }

    function it_should_get_a_file_setter()
    {
        $this->getFileSetter()->shouldBe(self::FILE_SETTER);
    }

    function it_should_get_a_file_getter()
    {
        $this->getFileGetter()->shouldBe(self::FILE_GETTER);
    }

    function it_should_get_an_uri_setter()
    {
        $this->getUriSetter()->shouldBe(self::URI_SETTER);
    }

    function it_should_get_a_fs_prefix_prop()
    {
        $this->getFilesystemPrefix()->shouldBe(self::FS_PREFIX);
    }

    function it_should_get_an_uri_format()
    {
        $this->getUriPrefix()->shouldBe(self::URI_PREFIX);
    }

    function it_should_get_a_fs_adapter()
    {
        $this->getFsAdapter()->shouldBe(self::FS_ADAPTER);
    }

    function it_should_get_a_naming_strategy()
    {
        $this->getNamingStrategy()->shouldBe(self::NAMING_STRATEGY);
    }

    function it_should_check_injectable_uri()
    {
        $this->isInjectableUri()->shouldBe(self::INJECT_URI_ON_LOAD);
    }

    function it_should_check_injectable_file_info()
    {
        $this->isInjectableFileInfo()->shouldBe(self::INJECT_FILE_INFO_ON_LOAD);
    }

    function it_should_check_deletable_on_remove()
    {
        $this->isDeletable()->shouldBe(self::DELETE_ON_REMOVE);
    }

    function it_should_check_deletable_old_file()
    {
        $this->isOldFileDeletable()->shouldBe(self::DELETE_OLD_FILE);
    }
}