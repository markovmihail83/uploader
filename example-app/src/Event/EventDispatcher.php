<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Event;

use Atom\Uploader\Event\IEventDispatcher;
use Atom\Uploader\Event\IUploadEvent;
use Atom\Uploader\Metadata\FileMetadata;
use ExampleApp\Subscriber\ISubscriber;

class EventDispatcher implements IEventDispatcher
{
    /**
     * @var string[callable[]]
     */
    private $listeners;

    /**
     * @var ISubscriber[]
     */
    private $subscribers;

    public function __construct()
    {
        $this->listeners = [
            IUploadEvent::PRE_UPLOAD => [],
            IUploadEvent::POST_UPLOAD => [],
            IUploadEvent::PRE_UPDATE => [],
            IUploadEvent::POST_UPDATE => [],
            IUploadEvent::PRE_REMOVE => [],
            IUploadEvent::POST_REMOVE => [],
            IUploadEvent::PRE_REMOVE_OLD_FILE => [],
            IUploadEvent::POST_REMOVE_OLD_FILE => [],
            IUploadEvent::PRE_INJECT_URI => [],
            IUploadEvent::POST_INJECT_URI => [],
            IUploadEvent::PRE_INJECT_FILE_INFO => [],
            IUploadEvent::POST_INJECT_FILE_INFO => [],
        ];

        $this->subscribers = [];
    }

    public function registerSubscriber(ISubscriber $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }

    public function registerListener($eventName, callable $listener)
    {
        $this->listeners[$eventName][] = $listener;
    }

    /**
     * @param string $eventName
     * @param object $fileReference
     * @param FileMetadata $metadata
     *
     * @return IUploadEvent
     */
    public function dispatch($eventName, $fileReference, FileMetadata $metadata)
    {
        $event = new UploadEvent($fileReference, $metadata);

        foreach ($this->listeners[$eventName] as $listener) {
            call_user_func($listener, $event);
        }

        foreach ($this->subscribers as $subscriber) {
            $events = $subscriber->getSubscribedEvents();

            if (!isset($events[$eventName])) {
                continue;
            }

            call_user_func([$subscriber, $events[$eventName]], $event);
        }

        return $event;
    }
}
