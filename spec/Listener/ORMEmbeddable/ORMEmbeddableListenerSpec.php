<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>.
 */

namespace spec\Atom\Uploader\Listener\ORMEmbeddable;

use Atom\Uploader\Handler\EventHandler;
use Atom\Uploader\Listener\ORMEmbeddable\ORMEmbeddableListener;
use Atom\Uploader\Model\Embeddable\FileReference;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin ORMEmbeddableListener
 */
class ORMEmbeddableListenerSpec extends ObjectBehavior
{
    const FILE_REFERENCE_PROPERTY = 'file_field';
    private $events;

    public function let(
        EventHandler $handler,
        PreUpdateEventArgs $event,
        FileReference $fileReference,
        FileReference $oldFileReference,
        $entity,
        $notFileReferenceEntity,
        EntityManagerInterface $em,
        ClassMetadata $metadata,
        ClassMetadata $embeddedMetadata,
        \ReflectionClass $embeddedReflection
    ) {
        $this->events = [
            Events::prePersist,
            Events::postPersist,
            Events::preUpdate,
            Events::postUpdate,
            Events::postLoad,
            Events::postRemove,
            Events::postFlush,
        ];

        $entityClass = get_class($entity->getWrappedObject());
        $fileReferenceProperties = [
            $entityClass => [
                self::FILE_REFERENCE_PROPERTY,
            ],
        ];

        $this->beConstructedWith($handler, $fileReferenceProperties, $this->events);

        $event->getEntity()->willReturn($entity);
        $event->getEntityManager()->willReturn($em);
        $em->getClassMetadata($entityClass)->willReturn($metadata);
        $em->getClassMetadata(FileReference::class)->willReturn($embeddedMetadata);

        $event->hasChangedField(self::FILE_REFERENCE_PROPERTY)->willReturn(true);
        $event->getOldValue(self::FILE_REFERENCE_PROPERTY)->willReturn($oldFileReference);
        $event->getNewValue(self::FILE_REFERENCE_PROPERTY)->willReturn($fileReference);

        $embeddedMetadata->getReflectionClass()->willReturn($embeddedReflection);
        $embeddedReflection->newInstanceWithoutConstructor()->willReturn($oldFileReference);

        $event->getEntityChangeSet()->willReturn([]);

        $metadata->getFieldMapping(self::FILE_REFERENCE_PROPERTY.'.file')->willReturn([
            'originalClass' => FileReference::class,
            'originalField' => 'file',
        ]);

        $metadata->getFieldValue($entity, self::FILE_REFERENCE_PROPERTY)->willReturn($fileReference);

        $metadata
            ->getFieldValue($entity, Argument::not(self::FILE_REFERENCE_PROPERTY))
            ->willReturn($notFileReferenceEntity);
    }

    public function it_should_get_events()
    {
        $this->getSubscribedEvents()->shouldEqual($this->events);
    }

    public function it_should_get_an_embedded_file_reference_from_old_values_if_that_has_been_updated_but_not_replaced(
        $event,
        $handler,
        $fileReference,
        $embeddedMetadata,
        $oldFileReference
    ) {
        $event->getEntityChangeSet()->willReturn([
            self::FILE_REFERENCE_PROPERTY.'.file' => ['old-file', 'new-file'],
            'another-field' => ['old-value', 'new-value'],
        ]);

        $event->hasChangedField(self::FILE_REFERENCE_PROPERTY)->willReturn(false);
        $embeddedMetadata->setFieldValue($oldFileReference, 'file', 'old-file')->shouldBeCalled();

        $handler
            ->preUpdate(Argument::type('string'), $fileReference, $oldFileReference)
            ->shouldBeCalled();

        $this->preUpdate($event);
    }

    public function it_should_do_nothing_if_the_entity_is_not_a_file_reference($handler, $event, $notFileReferenceEntity)
    {
        $event->getEntity()->willReturn($notFileReferenceEntity);
        $handler->prePersist(Argument::any(), Argument::any())->shouldNotBeCalled();
        $handler->postPersist(Argument::any())->shouldNotBeCalled();
        $handler->preUpdate(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();
        $handler->postUpdate(Argument::any())->shouldNotBeCalled();
        $handler->postRemove(Argument::any())->shouldNotBeCalled();
        $handler->postLoad(Argument::any())->shouldNotBeCalled();

        $this->prePersist($event);
        $this->postPersist($event);
        $this->preUpdate($event);
        $this->postUpdate($event);
        $this->postLoad($event);
        $this->postRemove($event);
    }

    public function it_should_delegate_events_to_the_handler(
        EventHandler $handler,
        $event,
        $fileReference,
        $oldFileReference
    ) {
        $id = Argument::type('string');

        $handler->prePersist($id, $fileReference)->shouldBeCalled();
        $handler->postPersist($id)->shouldBeCalled();
        $handler->preUpdate($id, $fileReference, $oldFileReference)->shouldBeCalled();
        $handler->postUpdate($id)->shouldBeCalled();
        $handler->postRemove($fileReference)->shouldBeCalled();
        $handler->postLoad($fileReference)->shouldBeCalled();
        $handler->postFlush()->shouldBeCalled();

        $this->prePersist($event);
        $this->postPersist($event);
        $this->preUpdate($event);
        $this->postUpdate($event);
        $this->postLoad($event);
        $this->postRemove($event);
        $this->postFlush();
    }
}
