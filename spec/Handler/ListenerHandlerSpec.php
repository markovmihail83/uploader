<?php

namespace spec\Atom\Uploader\Handler;


use Atom\Uploader\DependencyInjection\IContainer;
use Atom\Uploader\Handler\ListenerHandler;
use Atom\Uploader\Handler\UploadHandler;
use Atom\Uploader\Model\Embeddable\FileReference;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin ListenerHandler
 */
class ListenerHandlerSpec extends ObjectBehavior
{
    function let(IContainer $container, UploadHandler $handler, FileReference $fileReference, FileReference $oldFileReference)
    {
        $container->getUploadHandler()->willReturn($handler);
        $this->beConstructedWith($container);

        $handler->hasUploadedFile(Argument::type(FileReference::class))->willReturn(true);
        $handler->isEqualFiles($fileReference, $oldFileReference)->willReturn(false);
        $handler->move(Argument::type(FileReference::class), Argument::any())->willReturn(null);
        $handler->delete(Argument::type(FileReference::class), Argument::any())->willReturn(true);
        $handler->injectUri(Argument::type(FileReference::class))->willReturn(null);
        $handler->injectFileInfo(Argument::type(FileReference::class))->willReturn(null);
    }

    function it_should_do_nothing_when_no_file_reference($container, $oldFileReference, $fileReference)
    {
        $container->getUploadHandler()->shouldNotBeCalled();
        $id = uniqid();
        
        $this->prePersist($id, null);
        $this->postPersist($id);
        $this->preUpdate($id, null, null);
        $this->preUpdate($id, $fileReference, null);
        $this->preUpdate($id, null, $oldFileReference);
        $this->postUpdate($id);
        $this->postLoad(null);
        $this->postRemove(null);
        $this->postFlush();
    }

    function it_should_do_nothing_if_file_reference_has_no_uploaded_file($handler, $fileReference, $oldFileReference)
    {
        $handler->hasUploadedFile($fileReference)->willReturn(false);
        $handler->move(Argument::any(), Argument::any())->shouldNotBeCalled();
        $handler->delete(Argument::any(), Argument::any())->shouldNotBeCalled();
        $id = uniqid(); 
        
        $this->prePersist($id, $fileReference);
        $this->preUpdate($id, $fileReference, $oldFileReference);
    }

    function it_should_move_file_on_prePersist($handler, $fileReference)
    {
        $handler->move($fileReference, Argument::any())->shouldBeCalled();
        $this->prePersist(uniqid(), $fileReference);
    }

    function it_should_move_file_on_preUpdate($handler, $fileReference, $oldFileReference)
    {
        $handler->move($fileReference, Argument::any())->shouldBeCalled();
        $this->preUpdate(uniqid(), $fileReference, $oldFileReference);
    }

    function it_should_delete_old_file($handler, $fileReference, $oldFileReference)
    {
        $handler->delete($oldFileReference, true)->shouldBeCalled();
        $id = uniqid();
        
        $this->preUpdate($id, $fileReference, $oldFileReference);
        $this->postUpdate($id);
    }

    function it_should_delete_file($handler, $fileReference)
    {
        $handler->delete($fileReference, Argument::any())->shouldBeCalled();
        $this->postRemove($fileReference);
    }

    function it_should_delete_file_when_failure_on_persist($handler, $fileReference)
    {
        $handler->delete($fileReference, Argument::any())->shouldBeCalled();
        $this->prePersist(uniqid(), $fileReference);
        $this->postFlush();
    }

    function it_should_not_delete_when_success_on_persist($handler, $fileReference)
    {
        $handler->delete($fileReference, Argument::any())->shouldNotBeCalled();
        $id = uniqid();
        
        $this->prePersist($id, $fileReference);
        $this->postPersist($id);
        $this->postFlush();
    }

    function it_should_rollback_when_failure_on_update($handler, $fileReference, $oldFileReference)
    {
        $handler->delete($oldFileReference, Argument::any())->shouldNotBeCalled();
        $handler->delete($fileReference, Argument::any())->shouldBeCalled();
        $this->preUpdate(uniqid(), $fileReference, $oldFileReference);
        $this->postFlush();
    }

    function it_should_delete_old_file_but_not_new_file($handler, $fileReference, $oldFileReference)
    {
        $handler->delete($oldFileReference, Argument::any())->shouldBeCalled();
        $handler->delete($fileReference, Argument::any())->shouldNotBeCalled();
        $id = uniqid();
        
        $this->preUpdate($id, $fileReference, $oldFileReference);
        $this->postUpdate($id);
        $this->postFlush();
    }

    function it_should_inject_uri_and_file_info_on_load($handler, $fileReference)
    {
        $handler->injectUri($fileReference)->shouldBeCalled();
        $handler->injectFileInfo($fileReference)->shouldBeCalled();
        $this->postLoad($fileReference);
    }
}