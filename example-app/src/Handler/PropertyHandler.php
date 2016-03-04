<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Handler;


use Atom\Uploader\Handler\IPropertyHandler;
use Atom\Uploader\Metadata\FileMetadata;
use Atom\Uploader\Model\Embeddable\FileReference;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PropertyHandler implements IPropertyHandler
{
    private $accessor;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param object|FileReference $fileReference
     * @param FileMetadata $metadata
     *
     * @return \SplFileInfo|string|null
     */
    public function getFile($fileReference, FileMetadata $metadata)
    {
        return $this->accessor->getValue($fileReference, $metadata->getFileGetter());
    }

    /**
     * @param object|FileReference $fileReference
     * @param FileMetadata $metadata
     * @param string|null $value
     */
    public function setFile($fileReference, FileMetadata $metadata, $value)
    {
        $this->accessor->setValue($fileReference, $metadata->getFileSetter(), $value);
    }

    /**
     * @param object|FileReference $fileReference
     * @param FileMetadata $metadata
     * @param \SplFileInfo|null $value
     */
    public function setFileInfo($fileReference, FileMetadata $metadata, $value)
    {
        $this->accessor->setValue($fileReference, $metadata->getFileInfoSetter(), $value);
    }

    /**
     * @param object|FileReference $fileReference
     * @param FileMetadata $metadata
     * @param string|null $value
     */
    public function setUri($fileReference, FileMetadata $metadata, $value)
    {
        $this->accessor->setValue($fileReference, $metadata->getUriSetter(), $value);
    }
}