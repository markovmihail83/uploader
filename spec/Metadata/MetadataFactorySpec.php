<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace spec\Atom\Uploader\Metadata;

use Atom\Uploader\Exception\NoSuchMetadataException;
use Atom\Uploader\Metadata\FileMetadata;
use Atom\Uploader\Metadata\MetadataFactory;
use Atom\Uploader\Model\Embeddable\FileReference;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin MetadataFactory
 */
class MetadataFactorySpec extends ObjectBehavior
{
    function let(FileMetadata $metadata)
    {
        $metadata->getFileGetter()->willReturn('file');
        $metadata->getFileSetter()->willReturn('file');
        $metadata->getUriSetter()->willReturn('uri');
        $metadata->getFileInfoSetter()->willReturn('fileInfo');
        $metadata->getFilesystemPrefix()->willReturn('fs_prefix');
        $metadata->getUriPrefix()->willReturn('/uploads/%s');
        $metadata->getFsAdapter()->willReturn('my_filesystem');
        $metadata->getNamingStrategy()->willReturn('my_namer');
        $metadata->isOldFileDeletable()->willReturn(true);
        $metadata->isDeletable()->willReturn(true);
        $metadata->isInjectableUri()->willReturn(true);
        $metadata->isInjectableFileInfo()->willReturn(true);

        $fileReferenceClasses = [
            FileReference::class => 0
        ];

        $metadataMap = [
            0 => $metadata
        ];

        $this->beConstructedWith($fileReferenceClasses, $metadataMap);
    }

    function it_should_check_existence_of_metadata($nonExistentMetadata)
    {
        $this->shouldHaveMetadata(FileReference::class);
        $this->shouldNotHaveMetadata($nonExistentMetadata);
    }

    function it_should_get_metadata()
    {
        $this->getMetadata(FileReference::class)->shouldBeAnInstanceOf(FileMetadata::class);
    }

    function it_should_throw_exception_when_getting_a_metadata($nonExistentMetadata)
    {
        $this->shouldThrow(NoSuchMetadataException::class)->duringGetMetadata($nonExistentMetadata);
    }
}