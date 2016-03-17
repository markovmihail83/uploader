<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>.
 */

namespace ExampleApp\Subscriber;

use Atom\Uploader\Event\IUploadEvent;

class StopOnInjectFileInfo extends StopAction
{
    /**
     * @return string
     */
    public function getEventName()
    {
        return IUploadEvent::PRE_INJECT_FILE_INFO;
    }
}
