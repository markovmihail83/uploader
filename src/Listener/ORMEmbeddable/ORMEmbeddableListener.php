<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Listener\ORMEmbeddable;

use Atom\Uploader\Handler\Uploader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * @SuppressWarnings(PHPMD.StaticAccess, PHPMD.LongVariable, PHPMD.LongVariable, PHPMD.ShortVariable, PHPMD.ElseExpression)
 */
class ORMEmbeddableListener implements EventSubscriber
{
    private $uploader;

    private $fileReferenceProperties;

    private $events;

    /**
     * ORMEmbeddableListener constructor.
     *
     * @param Uploader $uploader
     * @param array $fileReferenceProperties Map of properties that is a file reference.
     *                                              e.g.: [entityClassName => [property1, property2, ...]]
     *                                              note:
     *                                              the "property1, property2, ..." must be property's name that is
     *                                              a file reference(which defined in the mappings).
     * @param array $events doctrine subscribed events
     */
    public function __construct(Uploader $uploader, array $fileReferenceProperties, array $events)
    {
        $this->uploader = $uploader;
        $this->fileReferenceProperties = $fileReferenceProperties;
        $this->events = $events;
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $id = $this->getIdentity($entity, $field);
            $fileReference = $this->getFieldValue($event, $field);

            $this->uploader->persist($id, $fileReference);
        }
    }

    private function getFileReferenceFields($entity)
    {
        $className = ClassUtils::getClass($entity);

        return isset($this->fileReferenceProperties[$className]) ? $this->fileReferenceProperties[$className] : [];
    }

    private function getIdentity($entity, $field)
    {
        return sprintf('%s_%s', spl_object_hash($entity), $field);
    }

    private function getFieldValue(LifecycleEventArgs $event, $field)
    {
        $entity = $event->getEntity();
        if (strpos($field, '.') === false) {
            $metadata = $event->getEntityManager()->getClassMetadata(ClassUtils::getClass($entity));

            return $metadata->getFieldValue($entity, $field);
        }

        $value = $entity;
        foreach (explode('.', $field) as $part) {
            $metadata = $event->getEntityManager()->getClassMetadata(ClassUtils::getClass($value));
            if (!$value = $metadata->getFieldValue($value, $part)) {
                return null;
            }
        }

        return $value;
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $this->uploader->saved($this->getIdentity($entity, $field));
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
                $oldFileReference = $this->getOldValues($event, $field);
            }

            $id = $this->getIdentity($entity, $field);
            $this->uploader->update($id, $newFileReference, $oldFileReference);
        }
    }

    private function getOldValues(PreUpdateEventArgs $event, $fieldName)
    {
        $oldValue = [];
        $fieldLength = strlen($fieldName) + 1;

        foreach ($event->getEntityChangeSet() as $name => $field) {
            if (false === strpos($name, $fieldName)) {
                continue;
            }

            $oldValue[substr($name, $fieldLength)] = $field[0];
        }

        return $oldValue ?: null;
    }

    public function postUpdate(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $this->uploader->updated($this->getIdentity($entity, $field));
        }
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $fileReference = $this->getFieldValue($event, $field);
            $this->uploader->loaded($fileReference);
        }
    }

    public function postRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        foreach ($this->getFileReferenceFields($entity) as $field) {
            $fileReference = $this->getFieldValue($event, $field);
            $this->uploader->removed($fileReference);
        }
    }

    public function postFlush()
    {
        $this->uploader->flush();
    }

    public function getSubscribedEvents()
    {
        return $this->events;
    }
}
