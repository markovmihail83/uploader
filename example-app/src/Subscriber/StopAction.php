<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Subscriber;


use Atom\Uploader\Event\IUploadEvent;

abstract class StopAction implements ISubscriber
{
    /**
     * @return string
     */
    abstract function getEventName();

    public function stopAction(IUploadEvent $event)
    {
        $event->stopAction();
    }

    public function getSubscribedEvents()
    {
        return [
            $this->getEventName() => 'stopAction'
        ];
    }
}