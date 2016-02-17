<?php


namespace Atom\Uploader\Event;

use Atom\Uploader\Metadata\FileMetadata;

trait UploadEvent
{
    private $fileReference;

    private $metadata;

    private $actionStopped;

    private $onUpdate;

    public function __construct($fileReference, FileMetadata $metadata, $onUpdate = false) {
        $this->fileReference = $fileReference;
        $this->metadata = $metadata;
        $this->actionStopped = false;
        $this->onUpdate = $onUpdate;
    }

    public function isUpdating() {
        return $this->onUpdate;
    }

    public function stopAction() {
        $this->actionStopped = true;
    }

    public function isActionStopped() {
        return $this->actionStopped;
    }

    public function getFileReference() {
        return $this->fileReference;
    }

    public function getMetadata() {
        return $this->metadata;
    }
}