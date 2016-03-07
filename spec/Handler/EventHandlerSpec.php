<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace spec\Atom\Uploader\Handler;


use Atom\Uploader\Handler\EventHandler;
use Atom\Uploader\Handler\UploadHandler;
use Atom\Uploader\LazyLoad\IUploadHandlerLazyLoader;
use Atom\Uploader\Model\Embeddable\FileReference;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin EventHandler
 */
class EventHandlerSpec extends ObjectBehavior
{
    function let(
        IUploadHandlerLazyLoader $uploadHandlerLazyLoader,
        UploadHandler $handler,
        FileReference $fileReference,
        FileReference $oldFileReference
    )
    {
        $uploadHandlerLazyLoader->getUploadHandler()->willReturn($handler);
        $this->beConstructedWith($uploadHandlerLazyLoader);

        $handler->hasUploadedFile(Argument::type(FileReference::class))->willReturn(true);
        $handler->isFilesEqual($fileReference, $oldFileReference)->willReturn(false);
        $handler->upload(Argument::type(FileReference::class))->willReturn(null);
        $handler->update(Argument::type(FileReference::class))->willReturn(null);
        $handler->deleteOldFile(Argument::type(FileReference::class))->willReturn(true);
        $handler->delete(Argument::type(FileReference::class))->willReturn(true);
        $handler->injectUri(Argument::type(FileReference::class))->willReturn(null);
        $handler->injectFileInfo(Argument::type(FileReference::class))->willReturn(null);
        $handler->isFileReference(Argument::type(FileReference::class))->willReturn(true);
        $handler->isFileReference(Argument::not(Argument::type(FileReference::class)))->willReturn(false);
    }

    function it_should_do_nothing_if_a_file_reference_is_none($uploadHandlerLazyLoader, $oldFileReference)
    {
        $uploadHandlerLazyLoader->getUploadHandler()->shouldNotBeCalled();
        $id = uniqid();

        $this->prePersist($id, null);
        $this->postPersist($id);
        $this->preUpdate($id, null, null);
        $this->preUpdate($id, null, $oldFileReference);
        $this->postUpdate($id);
        $this->postLoad(null);
        $this->postRemove(null);
        $this->postFlush();
    }

    function it_should_do_nothing_if_an_object_is_not_a_file_reference($object, $oldFileReference)
    {
        $id = uniqid();

        $this->prePersist($id, $object);
        $this->postPersist($id);
        $this->prePersist($id, $object);
        $this->preUpdate($id, $object, $oldFileReference);
        $this->postUpdate($id);
        $this->postLoad($object);
        $this->postRemove($object);
        $this->postFlush();
    }

    function it_should_do_nothing_if_a_file_reference_is_not_have_an_uploaded_file(
        $handler,
        $fileReference,
        $oldFileReference
    )
    {
        $handler->hasUploadedFile($fileReference)->willReturn(false);
        $handler->upload(Argument::any())->shouldNotBeCalled();
        $handler->update(Argument::any())->shouldNotBeCalled();
        $id = uniqid();

        $this->prePersist($id, $fileReference);
        $this->preUpdate($id, $fileReference, $oldFileReference);
    }

    function it_should_upload_a_file($handler, $fileReference)
    {
        $handler->upload($fileReference)->shouldBeCalled();
        $this->prePersist(uniqid(), $fileReference);
    }

    function it_should_update_a_file($handler, $fileReference, $oldFileReference)
    {
        $handler->update($fileReference)->shouldBeCalled();
        $this->preUpdate(uniqid(), $fileReference, $oldFileReference);
    }

    function it_should_delete_an_old_file($handler, $fileReference, $oldFileReference)
    {
        $handler->deleteOldFile($oldFileReference)->shouldBeCalled();
        $id = uniqid();
        $this->preUpdate($id, $fileReference, $oldFileReference);
        $this->postUpdate($id);
    }

    function it_should_not_delete_an_old_file_if_old_file_is_none($handler, $fileReference)
    {
        $handler->isFilesEqual($fileReference, Argument::any())->shouldNotBeCalled(false);
        $handler->deleteOldFile(Argument::any())->shouldNotBeCalled();
        $id = uniqid();
        $this->preUpdate($id, $fileReference, null);
        $this->postUpdate($id);
    }

    function it_should_not_delete_an_old_file_if_that_is_equal_to_a_new_file(
        $handler,
        $fileReference,
        $oldFileReference
    )
    {
        $handler->isFilesEqual($fileReference, $oldFileReference)->willReturn(true);
        $handler->deleteOldFile(Argument::any())->shouldNotBeCalled();
        $id = uniqid();
        $this->preUpdate($id, $fileReference, $oldFileReference);
        $this->postUpdate($id);
    }

    function it_should_delete_a_file($handler, $fileReference)
    {
        $handler->delete($fileReference)->shouldBeCalled();
        $this->postRemove($fileReference);
    }

    function it_should_delete_a_file_on_persist_failure($handler, $fileReference)
    {
        $handler->delete($fileReference)->shouldBeCalled();
        $this->prePersist(uniqid(), $fileReference);
        $this->postFlush();
    }

    function it_should_not_delete_file_on_persist_success($handler, $fileReference)
    {
        $handler->delete($fileReference)->shouldNotBeCalled();
        $id = uniqid();
        $this->prePersist($id, $fileReference);
        $this->postPersist($id);
        $this->postFlush();
    }

    function it_should_rollback_on_update_failure($handler, $fileReference, $oldFileReference)
    {
        $handler->deleteOldFile($oldFileReference)->shouldNotBeCalled();
        $handler->delete($fileReference)->shouldBeCalled();
        $this->preUpdate(uniqid(), $fileReference, $oldFileReference);
        $this->postFlush();
    }

    function it_should_delete_an_old_file_on_update_success($handler, $fileReference, $oldFileReference)
    {
        $handler->deleteOldFile($oldFileReference)->shouldBeCalled();
        $id = uniqid();

        $this->preUpdate($id, $fileReference, $oldFileReference);
        $this->postUpdate($id);
        $this->postFlush();
    }

    function it_should_inject_an_uri_and_a_file_info_on_load($handler, $fileReference)
    {
        $handler->injectUri($fileReference)->shouldBeCalled();
        $handler->injectFileInfo($fileReference)->shouldBeCalled();
        $this->postLoad($fileReference);
    }
}