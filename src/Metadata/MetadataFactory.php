<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Metadata;


use Atom\Uploader\Exception\NoSuchMetadataException;

class MetadataFactory
{
    private $metadataMap;

    private $fileReferenceClasses;

    public function __construct(array $fileReferenceClasses, array $metadataMap)
    {
        $this->fileReferenceClasses = $fileReferenceClasses;
        $this->metadataMap = $metadataMap;
    }

    public function getMetadata($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }

        if (!isset($this->fileReferenceClasses[$className])) {
            throw new NoSuchMetadataException($className);
        }

        $metadataIndex = $this->fileReferenceClasses[$className];
        $metadata = $this->metadataMap[$metadataIndex];

        return $metadata;
    }

    public function hasMetadata($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }

        return isset($this->fileReferenceClasses[$className]);
    }
}