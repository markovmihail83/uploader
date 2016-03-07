<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Metadata;


use Atom\Uploader\Exception\NoSuchMetadataException;

class MetadataFactory
{
    private $metadataMap;

    private $classNamesForMetadata;

    /**
     * @param array $classNamesForMetadata
     * @param FileMetadata[] $metadataMap
     */
    public function __construct(array $classNamesForMetadata, array $metadataMap)
    {
        $this->classNamesForMetadata = $classNamesForMetadata;
        $this->metadataMap = $metadataMap;
    }

    public function getMetadata($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }

        if (!isset($this->classNamesForMetadata[$className])) {
            throw new NoSuchMetadataException($className);
        }

        $metadataName = $this->classNamesForMetadata[$className];

        return $this->metadataMap[$metadataName];
    }

    public function hasMetadata($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }

        return isset($this->classNamesForMetadata[$className]);
    }
}