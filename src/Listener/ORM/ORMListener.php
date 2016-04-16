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
        $entityClass = ClassUtils::getClass($entity);

        if (!isset($this->fileReferenceEntities[$entityClass])) {
            return;
        }

        $this->handler->persist(spl_object_hash($entity), $entity, $entityClass);
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        $entityClass = ClassUtils::getClass($entity);

        if (!isset($this->fileReferenceEntities[$entityClass])) {
            return;
        }

        $this->handler->saved(spl_object_hash($entity));
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $entity = $event->getEntity();
        $entityClass = ClassUtils::getClass($entity);

        if (!isset($this->fileReferenceEntities[$entityClass])) {
            return;
        }

        $this->handler->update(spl_object_hash($entity), $entity, $this->getOldValues($event), $entityClass);
    }

    public function postUpdate(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        $entityClass = ClassUtils::getClass($entity);

        if (!isset($this->fileReferenceEntities[$entityClass])) {
            return;
        }

        $this->handler->updated(spl_object_hash($entity));
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        $entityClass = ClassUtils::getClass($entity);

        if (!isset($this->fileReferenceEntities[$entityClass])) {
            return;
        }

        $this->handler->loaded($entity, $entityClass);
    }

    public function postRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        $entityClass = ClassUtils::getClass($entity);

        if (!isset($this->fileReferenceEntities[$entityClass])) {
            return;
        }

        $this->handler->removed($entity, $entityClass);
    }

    public function postFlush()
    {
        $this->handler->flush();
    }

    public function getSubscribedEvents()
    {
        return $this->events;
    }

    private function getOldValues(PreUpdateEventArgs $event)
    {
        $oldValues = [];

        foreach ($event->getEntityChangeSet() as $name => $field) {
            if (false !== strpos($name, '.')) {
                continue;
            }

            $oldValues[$name] = $field[0];
        }

        return $oldValues ?: null;
    }
}
