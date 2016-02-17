<?php

namespace Atom\Uploader\Metadata;


use Atom\Uploader\Exception\NoSuchMetadataException;

class MetadataFactory
{
    private $metadataCollection;

    private $metadataIdentityMap;

    public function __construct(array $metadataIds, array $metadataIdentityMap) {
        $this->metadataIdentityMap = $metadataIds;
        $this->metadataCollection = $metadataIdentityMap;
    }

    public function getMetadata($className) {
        if (is_object($className)) {
            $className = get_class($className);
        }

        if (!isset($this->metadataIdentityMap[$className])) {
            throw new NoSuchMetadataException($className);
        }

        $metadataIndex = $this->metadataIdentityMap[$className];
        $metadata = $this->metadataCollection[$metadataIndex];

        if (!$metadata instanceof FileMetadata) {
            $metadata = $this->createMetadata($metadata);
            $this->metadataCollection[$metadataIndex] = $metadata;
        }

        return $metadata;
    }

    public function hasMetadata($className) {
        if (is_object($className)) {
            $className = get_class($className);
        }

        return isset($this->metadataIdentityMap[$className]);
    }

    /**
     * @param array $metadata
     *
     * @return FileMetadata
     */
    private function createMetadata(array $metadata) {
        $newMetadata = new FileMetadata(
            $metadata['file_setter'],
            $metadata['file_getter'],
            $metadata['uri_setter'],
            $metadata['file_info_setter'],
            $metadata['filesystem_prefix'],
            $metadata['uri_prefix'],
            $metadata['storage_type'],
            $metadata['naming_strategy'],
            $metadata['delete_on_update'],
            $metadata['delete_on_remove'],
            $metadata['inject_uri_on_load'],
            $metadata['inject_file_info_on_load']
        );

        return $newMetadata;
    }
}