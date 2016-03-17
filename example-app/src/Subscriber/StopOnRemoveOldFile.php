<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Subscriber;

use Atom\Uploader\Event\IUploadEvent;

class StopOnRemoveOldFile extends StopAction
{
    /**
     * @return string
     */
    public function getEventName()
    {
        return IUploadEvent::PRE_REMOVE_OLD_FILE;
    }
}
