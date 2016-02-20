<?php


namespace spec\Atom\Uploader\Listener\ORMEmbeddable;


use Atom\Uploader\Handler\ListenerHandler;
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
    private $events;

    const FILE_REFERENCE_PROPERTY = 'file_field';

    function let(
        ListenerHandler $handler,
        PreUpdateEventArgs $event,
        FileReference $fileReference,
        FileReference $oldFileReference,
        $entity,
        $notFileReferenceEntity,
        EntityManagerInterface $em,
        ClassMetadata $metadata
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

        $fileReferenceProperties = [
            get_class($entity->getWrappedObject()) => [
                self::FILE_REFERENCE_PROPERTY
            ]
        ];

        $this->beConstructedWith($handler, $fileReferenceProperties, $this->events);

        $event->getEntity()->willReturn($entity);
        $event->getEntityManager()->willReturn($em);
        $em->getClassMetadata(Argument::type('string'))->willReturn($metadata);

        $event->getOldValue(self::FILE_REFERENCE_PROPERTY)->willReturn($oldFileReference);
        $event->getNewValue(self::FILE_REFERENCE_PROPERTY)->willReturn($fileReference);

        $event->hasChangedField(self::FILE_REFERENCE_PROPERTY)->willReturn(true);

        $metadata->getFieldValue($entity, self::FILE_REFERENCE_PROPERTY)->willReturn($fileReference);

        $metadata->getFieldValue(
            $entity,
            Argument::not(self::FILE_REFERENCE_PROPERTY)
        )->willReturn($notFileReferenceEntity);
    }

    function it_should_get_the_events()
    {
        $this->getSubscribedEvents()->shouldEqual($this->events);
    }

    function it_should_do_nothing_when_file_field_is_not_changed_on_update($event, $handler)
    {
        $event->hasChangedField(self::FILE_REFERENCE_PROPERTY)->willReturn(false);
        $handler->preUpdate(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->preUpdate($event);
    }

    function it_should_do_nothing_when_entity_is_not_file_reference($handler, $event, $notFileReferenceEntity)
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

    function it_should_call_same_method_of_listener_handler(ListenerHandler $handler, $event, $fileReference, $oldFileReference)
    {
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