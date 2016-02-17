<?php


namespace Atom\Uploader\Event;


use Atom\Uploader\Metadata\FileMetadata;

interface IEventDispatcher
{
    /**
     * @param string       $eventName
     *
     * @param object       $fileReference
     * @param FileMetadata $metadata
     * @param bool         $onUpdate
     *
     * @return IUploadEvent
     */
    public function dispatch($eventName, $fileReference, FileMetadata $metadata, $onUpdate = false);
}