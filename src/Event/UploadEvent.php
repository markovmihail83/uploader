<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Event;

use Atom\Uploader\Metadata\FileMetadata;

trait UploadEvent
{
    private $fileReference;

    private $metadata;

    private $actionStopped;

    public function __construct(&$fileReference, FileMetadata $metadata)
    {
        $this->fileReference = $fileReference;
        $this->metadata = $metadata;
        $this->actionStopped = false;
    }

    public function stopAction()
    {
        $this->actionStopped = true;
    }

    public function isActionStopped()
    {
        return $this->actionStopped;
    }

    public function &getFileReference()
    {
        return $this->fileReference;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }
}
