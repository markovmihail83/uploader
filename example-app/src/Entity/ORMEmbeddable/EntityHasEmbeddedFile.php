<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Entity\ORMEmbeddable;


use Atom\Uploader\Model\Embeddable\FileReference;

class EntityHasEmbeddedFile
{
    private $id;

    private $fileReference;

    public function __construct(FileReference $fileReference)
    {
        $this->fileReference = $fileReference;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return FileReference
     */
    public function getFileReference()
    {
        return $this->fileReference;
    }

    /**
     * @param FileReference $fileReference
     */
    public function setFileReference(FileReference $fileReference)
    {
        $this->fileReference = $fileReference;
    }
}