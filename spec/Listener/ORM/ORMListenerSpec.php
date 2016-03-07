<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace spec\Atom\Uploader\Listener\ORM;


use Atom\Uploader\Handler\EventHandler;
use Atom\Uploader\Listener\ORM\ORMListener;
use Atom\Uploader\Model\Embeddable\FileReference;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin ORMListener
 */
class ORMListenerSpec extends ObjectBehavior
{
    private $events;

    function let(
        EventHandler $handler,
        PreUpdateEventArgs $event,
        FileReference $fileReference,
        EntityManagerInterface $em,
        ClassMetadata $metadata
    )
    {
        $this->events = [
            Events::prePersist,
            Events::postPersist,
            Events::preUpdate,
            Events::postUpdate,
            Events::postLoad,
            Events::postRemove,
            Events::postFlush,
        ];

        $fileReferenceEntities = [
            get_class($fileReference->getWrappedObject()) => $fileReference
        ];

        $this->beConstructedWith($handler, $fileReferenceEntities, $this->events);

        $event->getEntity()->willReturn($fileReference);
        $event->getEntityManager()->willReturn($em);
        $em->getClassMetadata(Argument::type('string'))->willReturn($metadata);

        $event->getEntityChangeSet()->willReturn(
            [
                'file' => ['file', null],
                'some-embeddable.field' => [null, null],
                'uri' => ['file_info', null],
                'file_info' => ['uri', null],
            ]
        );

        $event->getOldValue('file')->willReturn('file');
        $event->getOldValue('file_info')->willReturn('file_info');
        $event->getOldValue('uri')->willReturn('uri');

        $metadata->setFieldValue(Argument::any(), Argument::type('string'), Argument::any())->willReturn(null);
    }

    function it_should_get_events()
    {
        $this->getSubscribedEvents()->shouldEqual($this->events);
    }

    function it_should_do_nothing_if_the_entity_is_not_a_file_reference($handler, $event, $notFileReference)
    {
        $event->getEntity()->willReturn($notFileReference);
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

    function it_should_delegate_events_to_the_handler(EventHandler $handler, $event, $fileReference)
    {
        $id = Argument::type('string');

        $handler->prePersist($id, $fileReference)->shouldBeCalled();
        $handler->postPersist($id)->shouldBeCalled();
        $handler->preUpdate($id, $fileReference, Argument::type(FileReference::class))->shouldBeCalled();
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