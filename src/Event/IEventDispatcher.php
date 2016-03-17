<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Event;

use Atom\Uploader\Metadata\FileMetadata;

interface IEventDispatcher
{
    /**
     * @param string $eventName
     * @param object $fileReference
     * @param FileMetadata $metadata
     *
     * @return IUploadEvent
     */
    public function dispatch($eventName, $fileReference, FileMetadata $metadata);
}
