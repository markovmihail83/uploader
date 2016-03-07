<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Listener\ORM;


use Atom\Uploader\Handler\EventHandler;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class ORMListener implements EventSubscriber
{
    private $handler;

    private $fileReferenceEntities;

    private $events;

    /**
     * ORMEmbeddableListener constructor.
     *
     * @param EventHandler $handler
     * @param array $fileReferenceEntities Map of entity classnames that is a file reference (which defined
     *                                               in the mappings).
     * @param array $events doctrine subscribed events
     */
    public function __construct(EventHandler $handler, array $fileReferenceEntities, array $events)
    {
        $this->handler = $handler;
        $this->fileReferenceEntities = $fileReferenceEntities;
        $this->events = $events;
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$this->isFileReference($entity)) {
            return;
        }

        $this->handler->prePersist($this->getFileId($entity), $entity);
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$this->isFileReference($entity)) {
            return;
        }

        $this->handler->postPersist($this->getFileId($entity));
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$this->isFileReference($entity)) {
            return;
        }

        $this->handler->preUpdate($this->getFileId($entity), $entity, $this->getOldEntity($event, $entity));
    }

    public function postUpdate(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$this->isFileReference($entity)) {
            return;
        }

        $this->handler->postUpdate($this->getFileId($entity));
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$this->isFileReference($entity)) {
            return;
        }

        $this->handler->postLoad($entity);
    }

    public function postRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$this->isFileReference($entity)) {
            return;
        }

        $this->handler->postRemove($entity);
    }

    public function postFlush()
    {
        $this->handler->postFlush();
    }

    private function getFileId($entity)
    {
        return spl_object_hash($entity);
    }

    private function getOldEntity(PreUpdateEventArgs $event, $entity)
    {
        $oldEntity = clone $entity;

        $metadata = $event->getEntityManager()->getClassMetadata(ClassUtils::getClass($entity));

        foreach ($event->getEntityChangeSet() as $name => $field) {
            if (false !== strpos($name, '.')) {
                continue;
            }

            $metadata->setFieldValue($oldEntity, $name, $field[0]);
        }

        return $oldEntity;
    }

    private function isFileReference($entity)
    {
        return isset($this->fileReferenceEntities[ClassUtils::getClass($entity)]);
    }

    public function getSubscribedEvents()
    {
        return $this->events;
    }
}