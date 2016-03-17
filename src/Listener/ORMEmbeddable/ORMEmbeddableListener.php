<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>.
 */

namespace Atom\Uploader\Listener\ORMEmbeddable;

use Atom\Uploader\Handler\EventHandler;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * @SuppressWarnings(PHPMD.StaticAccess, PHPMD.LongVariable, PHPMD.LongVariable, PHPMD.ShortVariable, PHPMD.ElseExpression)
 */
class ORMEmbeddableListener implements EventSubscriber
{
    private $handler;

    private $fileReferenceProperties;

    private $events;

    /**
     * ORMEmbeddableListener constructor.
     *
     * @param EventHandler $handler
     * @param array        $fileReferenceProperties Map of properties that is a file reference.
     *                                              e.g.: [entityClassName => [property1, property2, ...]]
     *                                              note:
     *                                              the "property1, property2, ..." must be property's name that is
     *                                              a file reference(which defined in the mappings).
     * @param array        $events                  doctrine subscribed events
     */
    public function __construct(EventHandler $handler, array $fileReferenceProperties, array $events)
    {
        $this->handler = $handler;
        $this->fileReferenceProperties = $fileReferenceProperties;
        $this->events = $events;
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $id = $this->getFileId($entity, $field);
            $fileReference = $this->getFieldValue($event, $field);

            $this->handler->prePersist($id, $fileReference);
        }
    }

    private function getFileReferenceFields($entity)
    {
        $className = ClassUtils::getClass($entity);

        return isset($this->fileReferenceProperties[$className]) ? $this->fileReferenceProperties[$className] : [];
    }

    private function getFileId($entity, $field)
    {
        return spl_object_hash($entity).'#'.$field;
    }

    private function getFieldValue(LifecycleEventArgs $event, $field)
    {
        $entity = $event->getEntity();
        $metadata = $event->getEntityManager()->getClassMetadata(ClassUtils::getClass($entity));

        return $metadata->getFieldValue($entity, $field);
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $this->handler->postPersist($this->getFileId($entity, $field));
        }
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            if ($event->hasChangedField($field)) {
                $newFileReference = $event->getNewValue($field);
                $oldFileReference = $event->getOldValue($field);
            } else {
                $newFileReference = $this->getFieldValue($event, $field);
                $oldFileReference = $this->getEmbeddedFieldFromOldValues($event, $field);
            }

            $id = $this->getFileId($entity, $field);
            $this->handler->preUpdate($id, $newFileReference, $oldFileReference);
        }
    }

    private function getEmbeddedFieldFromOldValues(PreUpdateEventArgs $event, $fieldName)
    {
        $oldValue = null;
        $em = $event->getEntityManager();
        $entityMetadata = $em->getClassMetadata(ClassUtils::getClass($event->getEntity()));
        $embeddedMetadata = null;

        foreach ($event->getEntityChangeSet() as $name => $field) {
            if (false === strpos($name, $fieldName)) {
                continue;
            }

            $mapping = $entityMetadata->getFieldMapping($name);

            if (!$embeddedMetadata) {
                $embeddedMetadata = $em->getClassMetadata($mapping['originalClass']);
            }

            if (!$oldValue) {
                $oldValue = $embeddedMetadata->getReflectionClass()->newInstanceWithoutConstructor();
            }

            $embeddedMetadata->setFieldValue($oldValue, $mapping['originalField'], $field[0]);
        }

        return $oldValue;
    }

    public function postUpdate(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $this->handler->postUpdate($this->getFileId($entity, $field));
        }
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $this->handler->postLoad($this->getFieldValue($event, $field));
        }
    }

    public function postRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $this->handler->postRemove($this->getFieldValue($event, $field));
        }
    }

    public function postFlush()
    {
        $this->handler->postFlush();
    }

    public function getSubscribedEvents()
    {
        return $this->events;
    }
}
