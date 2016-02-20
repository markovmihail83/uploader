<?php


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
    function let()
    {
        $metadataIds = [
            FileReference::class => 0
        ];

        $metadataIdentityMap = [
            0 => [
                'file_setter' => 'file',
                'file_getter' => 'file',
                'uri_setter' => 'uri',
                'file_info_setter' => 'fileInfo',
                'filesystem_prefix' => 'fs_prefix',
                'uri_prefix' => '/uploads/%s',
                'storage_type' => 'my_storage',
                'naming_strategy' => 'my_naming',
                'delete_on_update' => true,
                'delete_on_remove' => true,
                'inject_file_info_on_load' => false,
                'inject_uri_on_load' => true,
            ]
        ];

        $this->beConstructedWith($metadataIds, $metadataIdentityMap);
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