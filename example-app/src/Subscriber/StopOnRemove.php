<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Subscriber;


use Atom\Uploader\Event\IUploadEvent;

class StopOnRemove extends StopAction
{
    /**
     * @return string
     */
    function getEventName()
    {
        return IUploadEvent::PRE_REMOVE;
    }
}