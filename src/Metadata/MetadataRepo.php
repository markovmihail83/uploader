<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Metadata;

use Atom\Uploader\Exception\NoSuchMetadataException;

class MetadataRepo
{
    private $metadataMap;

    private $metadataNames;

    /**
     * @param array $metadataNames
     * @param FileMetadata[] $metadataMap
     */
    public function __construct(array $metadataNames, array $metadataMap)
    {
        $this->metadataNames = $metadataNames;
        $this->metadataMap = $metadataMap;
    }

    public function getMetadata($name)
    {
        if (is_object($name)) {
            $name = get_class($name);
        }

        if (!isset($this->metadataNames[$name])) {
            throw new NoSuchMetadataException($name);
        }

        $metadataName = $this->metadataNames[$name];

        return $this->metadataMap[$metadataName];
    }

    public function hasMetadata($name)
    {
        if (is_object($name)) {
            $name = get_class($name);
        }

        return isset($this->metadataNames[$name]);
    }
}
