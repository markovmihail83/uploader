<?php

namespace Atom\Uploader\Listener\ORMEmbeddable;


use Atom\Uploader\Handler\ListenerHandler;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class ORMEmbeddableListener implements EventSubscriber
{
    private $handler;

    private $fileReferenceProperties;

    private $events;

    /**
     * ORMEmbeddableListener constructor.
     *
     * @param ListenerHandler $handler
     * @param array           $fileReferenceProperties Map of properties that is a file reference.
     *                                                 e.g.: [entityClassName => [property1, property2, ...]]
     *                                                 note:
     *                                                 the "property1, property2, ..." must be property's name that is
     *                                                 a file reference(which defined in the mappings).
     * @param array           $events                  doctrine subscribed events
     */
    public function __construct(ListenerHandler $handler, array $fileReferenceProperties, array $events) {
        $this->handler = $handler;
        $this->fileReferenceProperties = $fileReferenceProperties;
        $this->events = $events;
    }

    public function prePersist(LifecycleEventArgs $event) {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $id = $this->getFileId($entity, $field);
            $fileReference = $this->getFieldValue($event, $field);

            $this->handler->prePersist($id, $fileReference);
        }
    }

    public function postPersist(LifecycleEventArgs $event) {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $this->handler->postPersist($this->getFileId($entity, $field));
        }
    }

    public function preUpdate(PreUpdateEventArgs $event) {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            if (!$event->hasChangedField($field)) {
                continue;
            }

            $id = $this->getFileId($entity, $field);
            $fileReference = $event->getNewValue($field);
            $oldFileReference = $event->getOldValue($field);

            $this->handler->preUpdate($id, $fileReference, $oldFileReference);
        }
    }

    public function postUpdate(LifecycleEventArgs $event) {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $this->handler->postUpdate($this->getFileId($entity, $field));
        }
    }

    public function postLoad(LifecycleEventArgs $event) {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $this->handler->postLoad($this->getFieldValue($event, $field));
        }
    }

    public function postRemove(LifecycleEventArgs $event) {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $this->handler->postRemove($this->getFieldValue($event, $field));
        }
    }

    public function postFlush() {
        $this->handler->postFlush();
    }

    private function getFileId($entity, $field) {
        return spl_object_hash($entity) . '#' . $field;
    }


    private function getFileReferenceFields($entity) {
        $className = ClassUtils::getClass($entity);

        return isset($this->fileReferenceProperties[$className]) ? $this->fileReferenceProperties[$className] : [];
    }

    private function getFieldValue(LifecycleEventArgs $event, $field) {
        $entity = $event->getEntity();
        $metadata = $event->getEntityManager()->getClassMetadata(ClassUtils::getClass($entity));

        return $metadata->getFieldValue($entity, $field);
    }

    public function getSubscribedEvents() {
        return $this->events;
    }
}