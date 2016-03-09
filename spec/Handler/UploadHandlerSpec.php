<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace spec\Atom\Uploader\Handler;


use Atom\Uploader\Event\IEventDispatcher;
use Atom\Uploader\Event\IUploadEvent;
use Atom\Uploader\Exception\FileCouldNotBeMovedException;
use Atom\Uploader\Handler\IPropertyHandler;
use Atom\Uploader\Handler\UploadHandler;
use Atom\Uploader\LazyLoad\IFilesystemFactoryLazyLoader;
use Atom\Uploader\Metadata\FileMetadata;
use Atom\Uploader\Metadata\MetadataRepo;
use Atom\Uploader\Model\Embeddable\FileReference;
use Atom\Uploader\Naming\INamer;
use Atom\Uploader\Naming\NamerFactory;
use Atom\Uploader\Filesystem\IFilesystem;
use Atom\Uploader\Filesystem\FilesystemFactory;
use org\bovigo\vfs\vfsStream;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument as Arg;
use spec\Atom\Uploader\Util\FilesystemHelper;
use PHPUnit_Framework_TestCase as Test;

/**
 * @mixin UploadHandler
 */
class UploadHandlerSpec extends ObjectBehavior
{
    use FilesystemHelper;

    private $fsPrefix;

    function let(
        MetadataRepo $metadataRepo,
        IPropertyHandler $propertyHandler,
        IFilesystemFactoryLazyLoader $filesystemFactoryLazyLoader,
        NamerFactory $namerFactory,
        IEventDispatcher $dispatcher,
        IUploadEvent $uploadEvent,
        FileReference $fileReference,
        FilesystemFactory $filesystemFactory,
        IFilesystem $filesystem,
        \SplFileInfo $fileInfo,
        FileMetadata $metadata,
        INamer $namer
    )
    {
        $this->mount();
        $this->fsPrefix = vfsStream::url(uniqid());

        $filesystemFactoryLazyLoader->getFilesystemFactory()->willReturn($filesystemFactory);
        $this->beConstructedWith($metadataRepo, $propertyHandler, $filesystemFactoryLazyLoader, $namerFactory, $dispatcher);

        $dispatcher->dispatch(Arg::type('string'), $fileReference, $metadata)->willReturn($uploadEvent);

        $metadata->getNamingStrategy()->willReturn('my_namer');
        $metadata->isDeletable()->willReturn(true);
        $metadata->isOldFileDeletable()->willReturn(true);
        $metadata->getUriPrefix()->willReturn('/uploads/%s');
        $metadata->getFsAdapter()->willReturn('my_filesystem');
        $metadata->getFilesystemPrefix()->willReturn($this->fsPrefix);
        $metadata->getUriSetter()->willReturn('uri');
        $metadata->isInjectableUri()->willReturn(true);
        $metadata->isInjectableFileInfo()->willReturn(true);

        $filesystemFactory->getFilesystem(Arg::type('string'))->willReturn($filesystem);
        $namerFactory->getNamer(Arg::type('string'))->willReturn($namer);
        $metadataRepo->getMetadata(Arg::any())->willReturn($metadata);
        $metadataRepo->hasMetadata(Arg::type(FileReference::class))->willReturn(true);
        $metadataRepo->hasMetadata(Arg::not(Arg::type(FileMetadata::class)))->willReturn(false);

        $filePath = self::joinPath($this->fsPrefix, uniqid());
        self::createFile($filePath);
        $fileInfo->__toString()->willReturn($filePath);
        $fileInfo->isWritable()->willReturn(true);

        $fileReference->__toString()->willReturn($filePath);

        $propertyHandler
            ->getFile(Arg::type(FileReference::class), Arg::type(FileMetadata::class))
            ->willReturn($fileInfo);

        $propertyHandler
            ->setFile(Arg::type(FileReference::class), Arg::type(FileMetadata::class), Arg::any())
            ->willReturn(null);

        $propertyHandler
            ->setUri(Arg::type(FileReference::class), Arg::type(FileMetadata::class), Arg::any())
            ->willReturn(null);

        $propertyHandler
            ->setFileInfo(Arg::type(FileReference::class), Arg::type(FileMetadata::class), Arg::any())
            ->willReturn(null);

        $uploadEvent->isActionStopped()->willReturn(false);

        $filesystem->delete($this->fsPrefix, Arg::type('string'))->willReturn(true);
        $filesystem->writeStream($this->fsPrefix, Arg::type('string'), Arg::any())->willReturn(true);
        $filesystem->resolveFileInfo($this->fsPrefix, Arg::type('string'))->willReturn($fileInfo);
        $namer->name(Arg::type(\SplFileInfo::class))->willReturn(uniqid());
    }

    function letGo()
    {
        $this->unMount();
    }

    function it_should_not_handle_a_file_reference_if_action_is_stopped_on_event(
        $fileReference,
        $filesystem,
        $uploadEvent,
        $propertyHandler
    )
    {
        $uploadEvent->isActionStopped()->willReturn(true);
        $this->upload($fileReference);
        $this->update($fileReference);
        $this->delete($fileReference)->shouldBe(false);
        $this->deleteOldFile($fileReference)->shouldBe(false);
        $this->injectUri($fileReference);
        $this->injectFileInfo($fileReference);

        $filesystem->writeStream(Arg::any(), Arg::any(), Arg::any())->shouldNotBeenCalled();
        $filesystem->delete($this->fsPrefix, Arg::any())->shouldNotBeenCalled();
        $propertyHandler->setFile($fileReference, Arg::any(), Arg::any())->shouldNotBeenCalled();
        $propertyHandler->setUri($fileReference, Arg::any(), Arg::any())->shouldNotBeenCalled();
        $propertyHandler->setFileInfo($fileReference, Arg::any(), Arg::any())->shouldNotBeenCalled();
    }

    function it_should_not_inject_an_uri_if_a_file_path_is_empty($fileReference, $propertyHandler)
    {
        $propertyHandler->getFile($fileReference, Arg::any())->willReturn('');
        $this->injectUri($fileReference);
        $propertyHandler->setUri(Arg::any(), Arg::any())->shouldNotBeenCalled();
    }

    function it_should_not_inject_an_uri_if_a_prefix_is_not_configured($fileReference, $propertyHandler, $metadata)
    {
        $metadata->getUriPrefix()->willReturn('');
        $propertyHandler->setUri(Arg::any(), Arg::any())->shouldNotBeCalled();
        $this->injectUri($fileReference);
    }

    function it_should_not_inject_an_uri_if_an_uri_setter_is_disabled($fileReference, $propertyHandler, $metadata)
    {
        $metadata->getUriSetter()->willReturn(false);
        $propertyHandler->setUri(Arg::any(), Arg::any())->shouldNotBeCalled();
        $this->injectUri($fileReference);
    }

    function it_should_not_inject_an_uri_if_an_uri_is_not_injectable($fileReference, $propertyHandler, $metadata)
    {
        $metadata->isInjectableUri()->willReturn(false);
        $propertyHandler->setUri(Arg::any(), Arg::any())->shouldNotBeCalled();
        $this->injectUri($fileReference);
    }

    function it_should_not_inject_a_file_info_if_a_file_path_is_empty($fileReference, $propertyHandler)
    {
        $propertyHandler->getFile($fileReference, Arg::any())->willReturn('');
        $propertyHandler->setFileInfo(Arg::any(), Arg::any())->shouldNotBeCalled();
        $this->injectFileInfo($fileReference);
    }

    function it_should_not_inject_a_file_info_if_a_file_info_setter_is_disabled(
        $fileReference,
        $propertyHandler,
        $metadata
    )
    {
        $metadata->getFileInfoSetter()->willReturn(false);
        $propertyHandler->setFileInfo(Arg::any(), Arg::any())->shouldNotBeCalled();
        $this->injectFileInfo($fileReference);
    }

    function it_should_not_inject_a_file_info_if_a_file_info_is_not_injectable(
        $fileReference,
        $propertyHandler,
        $metadata
    )
    {
        $metadata->isInjectableFileInfo()->willReturn(false);
        $propertyHandler->setFileInfo(Arg::any(), Arg::any())->shouldNotBeCalled();
        $this->injectFileInfo($fileReference);
    }

    function it_should_not_inject_a_file_info_if_a_file_info_is_not_resolved($fileReference, $propertyHandler, $filesystem)
    {
        $filesystem->resolveFileInfo($this->fsPrefix, Arg::type('string'))->willReturn(null);
        $propertyHandler->setFileInfo(Arg::any(), Arg::any())->shouldNotBeCalled();
        $this->injectFileInfo($fileReference);
    }

    function it_should_not_match_different_files($fileReference, $propertyHandler, FileReference $fileReference2)
    {
        $propertyHandler->getFile($fileReference, Arg::any())->willReturn('filename');
        $propertyHandler->getFile($fileReference2, Arg::any())->willReturn('another-filename');
        $this->shouldNotBeFilesEqual($fileReference, $fileReference2);
    }

    function it_should_match_equal_files($fileReference, $propertyHandler, FileReference $fileReference2)
    {
        $propertyHandler->getFile($fileReference, Arg::any())->willReturn('some-filename');
        $propertyHandler->getFile($fileReference2, Arg::any())->willReturn('some-filename');
        $this->shouldBeFilesEqual($fileReference, $fileReference2);
    }

    function it_should_not_delete_if_file_reference_does_not_have_a_file($fileReference, $propertyHandler)
    {
        $propertyHandler->getFile($fileReference, Arg::any())->willReturn(null);
        $this->delete($fileReference)->shouldBe(false);
    }

    function it_should_not_delete_if_that_could_not_delete_from_filesystem($fileReference, $filesystem, $propertyHandler)
    {
        $propertyHandler->getFile($fileReference, Arg::any())->willReturn('relative/file/path/on/filesystem');
        $filesystem->delete($this->fsPrefix, 'relative/file/path/on/filesystem')->willReturn(false);
        $this->delete($fileReference)->shouldBe(false);
    }

    function it_should_not_delete_if_file_reference_is_not_deletable($fileReference, $metadata)
    {
        $metadata->isDeletable()->willReturn(false);
        $this->delete($fileReference)->shouldBe(false);
    }

    function it_should_not_delete_old_file_if_old_file_is_not_deletable($fileReference, $metadata)
    {
        $metadata->isOldFileDeletable()->willReturn(false);
        $this->deleteOldFile($fileReference)->shouldBe(false);
    }

    function it_should_dispatch_on_upload($fileReference, $dispatcher)
    {
        $dispatcher->dispatch(IUploadEvent::PRE_UPLOAD, Arg::any(), Arg::any(), Arg::any())->shouldBeCalled();
        $dispatcher->dispatch(IUploadEvent::POST_UPLOAD, Arg::any(), Arg::any(), Arg::any())->shouldBeCalled();
        $this->upload($fileReference);
    }

    function it_should_dispatch_on_update($fileReference, $dispatcher)
    {
        $dispatcher->dispatch(IUploadEvent::PRE_UPDATE, Arg::any(), Arg::any(), Arg::any())->shouldBeCalled();
        $dispatcher->dispatch(IUploadEvent::POST_UPDATE, Arg::any(), Arg::any(), Arg::any())->shouldBeCalled();
        $this->update($fileReference);
    }

    function it_should_dispatch_on_delete($fileReference, $dispatcher)
    {
        $dispatcher->dispatch(IUploadEvent::PRE_REMOVE, Arg::any(), Arg::any(), Arg::any())->shouldBeCalled();
        $dispatcher->dispatch(IUploadEvent::POST_REMOVE, Arg::any(), Arg::any(), Arg::any())->shouldBeCalled();
        $this->delete($fileReference);
    }

    function it_should_dispatch_on_delete_an_old_file($fileReference, $dispatcher)
    {
        $dispatcher->dispatch(IUploadEvent::PRE_REMOVE_OLD_FILE, Arg::any(), Arg::any(), Arg::any())->shouldBeCalled();
        $dispatcher->dispatch(IUploadEvent::POST_REMOVE_OLD_FILE, Arg::any(), Arg::any(), Arg::any())->shouldBeCalled();
        $this->deleteOldFile($fileReference);
    }

    function it_should_dispatch_on_inject_an_uri($fileReference, $dispatcher)
    {
        $dispatcher->dispatch(IUploadEvent::PRE_INJECT_URI, Arg::any(), Arg::any(), Arg::any())->shouldBeCalled();
        $dispatcher->dispatch(IUploadEvent::POST_INJECT_URI, Arg::any(), Arg::any(), Arg::any())->shouldBeCalled();
        $this->injectUri($fileReference);
    }

    function it_should_dispatch_on_inject_a_file_info($fileReference, $dispatcher)
    {
        $dispatcher->dispatch(IUploadEvent::PRE_INJECT_FILE_INFO, Arg::any(), Arg::any(), Arg::any())->shouldBeCalled();
        $dispatcher
            ->dispatch(IUploadEvent::POST_INJECT_FILE_INFO, Arg::any(), Arg::any(), Arg::any())
            ->shouldBeCalled();
        $this->injectFileInfo($fileReference);
    }

    function it_should_delete_a_file($fileReference, $propertyHandler)
    {
        $propertyHandler->getFile($fileReference, Arg::any())->willReturn('relative/file/path/on/filesystem');
        $this->delete($fileReference)->shouldBe(true);
    }

    function it_should_delete_a_file_if_file_is_an_instance_of_splFileInfo($fileReference, $fileInfo)
    {
        $this->delete($fileReference)->shouldBe(true);
        Test::assertFalse(file_exists((string)$fileInfo->getWrappedObject()));
    }

    function it_should_not_have_an_uploaded_file_if_a_file_is_not_an_instance_of_splFileInfo(
        $fileReference,
        $propertyHandler
    )
    {
        $propertyHandler->getFile($fileReference, Arg::any())->willReturn('/it/is/not/instance/of/SplFileInfo');
        $this->shouldNotHaveUploadedFile($fileReference);
    }

    function it_should_have_an_uploaded_file($fileReference)
    {
        $this->shouldHaveUploadedFile($fileReference);
    }

    function it_should_throw_exception_on_upload($fileReference, $filesystem, $fileInfo)
    {
        $filesystem->writeStream($this->fsPrefix, Arg::type('string'), Arg::type('resource'))->willReturn(false);
        $this->shouldThrow(FileCouldNotBeMovedException::class)->duringUpload($fileReference);
        Test::assertTrue(file_exists((string)$fileInfo->getWrappedObject()));
    }

    function it_should_throw_exception_on_update($fileReference, $filesystem, $fileInfo)
    {
        $filesystem->writeStream($this->fsPrefix, Arg::type('string'), Arg::type('resource'))->willReturn(false);
        $this->shouldThrow(FileCouldNotBeMovedException::class)->duringUpdate($fileReference);
        Test::assertTrue(file_exists((string)$fileInfo->getWrappedObject()));
    }

    function it_should_upload_a_file($fileReference, $propertyHandler, $filesystem)
    {
        $filesystem->writeStream($this->fsPrefix, Arg::type('string'), Arg::any())->shouldBeCalled();
        $propertyHandler->setFile($fileReference, Arg::any(), Arg::type('string'))->shouldBeCalled();
        $this->upload($fileReference);
    }

    function it_should_update_a_file($fileReference, $propertyHandler, $filesystem)
    {
        $filesystem->writeStream($this->fsPrefix, Arg::type('string'), Arg::any())->shouldBeCalled();
        $propertyHandler->setFile($fileReference, Arg::any(), Arg::type('string'))->shouldBeCalled();
        $this->update($fileReference);
    }

    function it_should_check_whether_the_object_is_a_file_reference($fileReference, $metadataRepo)
    {
        $metadataRepo->hasMetadata(Arg::type(FileReference::class))->willReturn(true)->shouldBeCalled();
        $metadataRepo->hasMetadata(Arg::not(Arg::type(FileReference::class)))->willReturn(false)->shouldBeCalled();
        $this->shouldBeFileReference($fileReference);
        $this->shouldNotBeFileReference('/it/is/not/a/file/reference');
    }
}