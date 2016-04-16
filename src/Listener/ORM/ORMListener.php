<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Listener\ORM;

use Atom\Uploader\Handler\Uploader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * @SuppressWarnings(PHPMD.StaticAccess, PHPMD.LongVariable)
 */
class ORMListener implements EventSubscriber
{
    private $handler;

    private $fileReferenceEntities;

    private $events;

    /**
     * ORMEmbeddableListener constructor.
     *
     * @param Uploader $handler
     * @param array $fileReferenceEntities Map of entity classnames that is a file reference (which defined
     *                                            in the mappings).
     * @param array $events doctrine subscribed events
     */
    public function __construct(Uploader $handler, array $fileReferenceEntities, array $events)
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

        $this->handler->persist($this->getFileId($entity), $entity);
    }

    private function isFileReference($entity)
    {
        return isset($this->fileReferenceEntities[ClassUtils::getClass($entity)]);
    }

    private function getFileId($entity)
    {
        return spl_object_hash($entity);
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$this->isFileReference($entity)) {
            return;
        }

        $this->handler->saved($this->getFileId($entity));
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$this->isFileReference($entity)) {
            return;
        }

        $this->handler->update($this->getFileId($entity), $entity, $this->getOldEntity($event, $entity));
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

    public function postUpdate(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$this->isFileReference($entity)) {
            return;
        }

        $this->handler->updated($this->getFileId($entity));
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$this->isFileReference($entity)) {
            return;
        }

        $this->handler->loaded($entity);
    }

    public function postRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$this->isFileReference($entity)) {
            return;
        }

        $this->handler->removed($entity);
    }

    public function postFlush()
    {
        $this->handler->flush();
    }

    public function getSubscribedEvents()
    {
        return $this->events;
    }
}
