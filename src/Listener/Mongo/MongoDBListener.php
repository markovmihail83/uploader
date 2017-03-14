<?php
/**
 * Copyright © 2017 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Listener\MongoDB;

use Atom\Uploader\Handler\Uploader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;

/**
 * @SuppressWarnings(PHPMD.StaticAccess, PHPMD.LongVariable)
 */
class MongoDBListener implements EventSubscriber
{
    private $uploader;

    private $fileReferenceEntities;

    private $events;

    /**
     * ORMEmbeddableListener constructor.
     *
     * @param Uploader $uploader
     * @param array $fileReferenceEntities Map of entity classnames that is a file reference (which defined
     *                                            in the mappings).
     * @param array $events doctrine subscribed events
     */
    public function __construct(Uploader $uploader, array $fileReferenceEntities, array $events)
    {
        $this->uploader = $uploader;
        $this->fileReferenceEntities = $fileReferenceEntities;
        $this->events = $events;
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $document = $event->getDocument();
        $documentClass = ClassUtils::getClass($document);

        if (!isset($this->fileReferenceEntities[$documentClass])) {
            return;
        }

        $this->uploader->persist(spl_object_hash($document), $document, $documentClass);
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $document = $event->getDocument();
        $documentClass = ClassUtils::getClass($document);

        if (!isset($this->fileReferenceEntities[$documentClass])) {
            return;
        }

        $this->uploader->saved(spl_object_hash($document));
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $document = $event->getDocument();
        $documentClass = ClassUtils::getClass($document);

        if (!isset($this->fileReferenceEntities[$documentClass])) {
            return;
        }

        $this->uploader->update(spl_object_hash($document), $document, $this->getOldValues($event), $documentClass);
    }

    public function postUpdate(LifecycleEventArgs $event)
    {
        $document = $event->getDocument();
        $documentClass = ClassUtils::getClass($document);

        if (!isset($this->fileReferenceEntities[$documentClass])) {
            return;
        }

        $this->uploader->updated(spl_object_hash($document));
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $document = $event->getDocument();
        $documentClass = ClassUtils::getClass($document);

        if (!isset($this->fileReferenceEntities[$documentClass])) {
            return;
        }

        $this->uploader->loaded($document, $documentClass);
    }

    public function postRemove(LifecycleEventArgs $event)
    {
        $document = $event->getDocument();
        $documentClass = ClassUtils::getClass($document);

        if (!isset($this->fileReferenceEntities[$documentClass])) {
            return;
        }

        $this->uploader->removed($document, $documentClass);
    }

    public function postFlush()
    {
        $this->uploader->flush();
    }

    public function getSubscribedEvents()
    {
        return $this->events;
    }

    private function getOldValues(PreUpdateEventArgs $event)
    {
        $oldValues = [];

        foreach ($event->getDocumentChangeSet() as $name => $field) {
            if (false !== strpos($name, '.')) {
                continue;
            }

            $oldValues[$name] = $field[0];
        }

        return $oldValues ?: null;
    }
}
