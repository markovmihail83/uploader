<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Subscriber;

interface ISubscriber
{
    /**
     * @return array e.g: ['eventName' => 'method']
     *               'method' will be called on 'eventName' with 1 arg(instance of Atom\Uploader\Event\IUploadEvent)
     */
    public function getSubscribedEvents();
}
