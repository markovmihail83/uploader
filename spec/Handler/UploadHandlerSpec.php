<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace spec\Atom\Uploader\Handler;


use Atom\Uploader\DependencyInjection\IContainer;
use Atom\Uploader\Event\IEventDispatcher;
use Atom\Uploader\Event\IUploadEvent;
use Atom\Uploader\Exception\FileCouldNotBeMovedException;
use Atom\Uploader\Handler\IPropertyHandler;
use Atom\Uploader\Handler\UploadHandler;
use Atom\Uploader\Metadata\FileMetadata;
use Atom\Uploader\Metadata\MetadataFactory;
use Atom\Uploader\Model\Embeddable\FileReference;
use Atom\Uploader\Naming\INamer;
use Atom\Uploader\Naming\NamerFactory;
use Atom\Uploader\Storage\IStorage;
use Atom\Uploader\Storage\StorageFactory;
use org\bovigo\vfs\vfsStream;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
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
        MetadataFactory $metadataFactory,
        IPropertyHandler $propertyHandler,
        IContainer $container,
        NamerFactory $namerFactory,
        IEventDispatcher $dispatcher,
        IUploadEvent $uploadEvent,
        FileReference $fileReference,
        StorageFactory $storageFactory,
        IStorage $storage,
        \SplFileInfo $fileInfo,
        FileMetadata $metadata,
        INamer $namer
    ) {
        $this->mount();
        $this->fsPrefix = vfsStream::url(uniqid());
        
        $container->getStorageFactory()->willReturn($storageFactory);
        $this->beConstructedWith($metadataFactory, $propertyHandler, $container, $namerFactory, $dispatcher);

        $dispatcher->dispatch(
            Argument::type('string'),
            $fileReference,
            $metadata,
            Argument::any()
        )->willReturn($uploadEvent);

        $metadata->getNamingStrategy()->willReturn('my_namer');
        $metadata->isDeletable(Argument::any())->willReturn(true);
        $metadata->getUriPrefix()->willReturn('/uploads/%s');
        $metadata->getStorageType()->willReturn('my_storage');
        $metadata->getFilesystemPrefix()->willReturn($this->fsPrefix);
        $metadata->getUriSetter()->willReturn('uri');
        $metadata->isInjectableUri()->willReturn(true);
        $metadata->isInjectableFileInfo()->willReturn(true);

        $storageFactory->getStorage(Argument::type('string'))->willReturn($storage);
        $namerFactory->getNamer(Argument::type('string'))->willReturn($namer);
        $metadataFactory->getMetadata(Argument::any())->willReturn($metadata);

        $filePath = self::joinPath($this->fsPrefix, uniqid());
        self::createFile($filePath);
        $fileInfo->getRealPath()->willReturn($filePath);
        $fileInfo->__toString()->willReturn($filePath);
        $fileInfo->isWritable()->willReturn(true);

        $propertyHandler->getFile(
            Argument::type(FileReference::class),
            Argument::type(FileMetadata::class)
        )->willReturn($fileInfo);

        $propertyHandler->setFile(
            Argument::type(FileReference::class), 
            Argument::type(FileMetadata::class),
            Argument::any()
        )->willReturn(null);

        $propertyHandler->setUri(
            Argument::type(FileReference::class),
            Argument::type(FileMetadata::class),
            Argument::any()
        )->willReturn(null);

        $propertyHandler->setFileInfo(
            Argument::type(FileReference::class),
            Argument::type(FileMetadata::class),
            Argument::any()
        )->willReturn(null);

        $uploadEvent->isActionStopped()->willReturn(false);
        
        $storage->delete($this->fsPrefix, Argument::type('string'))->willReturn(true);
        $storage->writeStream($this->fsPrefix, Argument::type('string'), Argument::any())->willReturn(true);
        $storage->resolveFileInfo($this->fsPrefix, Argument::type('string'))->willReturn($fileInfo);
        $namer->name(Argument::type(\SplFileInfo::class))->willReturn(uniqid());
    }

    function letGo()
    {
        $this->unMount();
    }

    function it_should_stop_action_when_moving_file($fileReference, $storage, $uploadEvent, $propertyHandler)
    {
        $uploadEvent->isActionStopped()->willReturn(true);
        $storage->writeStream(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();
        $propertyHandler->setFile($fileReference, Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->move($fileReference);
    }

    function it_should_stop_action_when_deleting_file($fileReference, $storage, $uploadEvent, $propertyHandler)
    {
        $uploadEvent->isActionStopped()->willReturn(true);
        $storage->delete($this->fsPrefix, Argument::any())->shouldNotBeCalled();
        $propertyHandler->setFile($fileReference, Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->delete($fileReference)->shouldBe(false);
    }

    function it_should_stop_action_on_inject_an_uri($fileReference, $propertyHandler, $uploadEvent)
    {
        $uploadEvent->isActionStopped()->willReturn(true);
        $propertyHandler->setUri($fileReference, Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->injectUri($fileReference);
    }

    function it_should_stop_action_on_inject_a_file_info($fileReference, $propertyHandler, $uploadEvent)
    {
        $uploadEvent->isActionStopped()->willReturn(true);
        $propertyHandler->setFileInfo($fileReference, Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->injectFileInfo($fileReference);
    }

    function it_should_not_inject_uri_when_file_path_is_empty($fileReference, $propertyHandler)
    {
        $propertyHandler->getFile($fileReference, Argument::any())->willReturn('');
        $propertyHandler->setUri(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->injectUri($fileReference);
    }

    function it_should_not_inject_uri_when_prefix_is_not_configured($fileReference, $propertyHandler, $metadata)
    {
        $metadata->getUriPrefix()->willReturn('');
        $propertyHandler->setUri(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->injectUri($fileReference);
    }

    function it_should_not_inject_uri_when_uri_setter_is_disabled($fileReference, $propertyHandler, $metadata)
    {
        $metadata->getUriSetter()->willReturn(false);
        $propertyHandler->setUri(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->injectUri($fileReference);
    }

    function it_should_not_inject_uri_when_uri_is_not_injectable($fileReference, $propertyHandler, $metadata)
    {
        $metadata->isInjectableUri()->willReturn(false);
        $propertyHandler->setUri(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->injectUri($fileReference);
    }

    function it_should_not_inject_file_info_when_file_path_is_empty($fileReference, $propertyHandler)
    {
        $propertyHandler->getFile($fileReference, Argument::any())->willReturn('');
        $propertyHandler->setFileInfo(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->injectFileInfo($fileReference);
    }

    function it_should_not_inject_file_info_when_file_info_setter_is_disabled($fileReference, $propertyHandler, $metadata)
    {
        $metadata->getFileInfoSetter()->willReturn(false);
        $propertyHandler->setFileInfo(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->injectFileInfo($fileReference);
    }

    function it_should_not_inject_file_info_when_file_info_is_not_injectable($fileReference, $propertyHandler, $metadata)
    {
        $metadata->isInjectableFileInfo()->willReturn(false);
        $propertyHandler->setFileInfo(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->injectFileInfo($fileReference);
    }

    function it_should_not_inject_file_info_when_file_info_is_not_resolved($fileReference, $propertyHandler, $storage)
    {
        $storage->resolveFileInfo($this->fsPrefix, Argument::type('string'))->willReturn(null);
        $propertyHandler->setFileInfo(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->injectFileInfo($fileReference);
    }

    function it_should_not_be_equal_files_when_file_references_has_different_files(
        $fileReference,
        $propertyHandler,
        FileReference $fileReference2
    ) {
        $propertyHandler->getFile($fileReference, Argument::any())->willReturn('filename');
        $propertyHandler->getFile($fileReference2, Argument::any())->willReturn('another-filename');

        $this->shouldNotBeEqualFiles($fileReference, $fileReference2);
    }

    function it_should_be_equal_files($fileReference, $propertyHandler, FileReference $fileReference2)
    {
        $propertyHandler->getFile($fileReference, Argument::any())->willReturn('some-filename');
        $propertyHandler->getFile($fileReference2, Argument::any())->willReturn('some-filename');

        $this->shouldBeEqualFiles($fileReference, $fileReference2);
    }

    function it_should_not_delete_when_file_reference_has_no_file($fileReference, $propertyHandler)
    {
        $propertyHandler->getFile($fileReference, Argument::any())->willReturn(null);
        $this->delete($fileReference)->shouldBe(false);
    }

    function it_should_not_delete_when_could_not_delete_from_storage($fileReference, $storage, $propertyHandler)
    {
        $propertyHandler->getFile($fileReference, Argument::any())->willReturn('relative/file/path/on/storage');
        $storage->delete($this->fsPrefix, 'relative/file/path/on/storage')->willReturn(false);
        $this->delete($fileReference)->shouldBe(false);
    }

    function it_should_not_delete_when_file_reference_is_not_deletable_on_remove($fileReference, $metadata)
    {
        $metadata->isDeletable(false)->willReturn(false);
        $this->delete($fileReference, false)->shouldBe(false);
    }

    function it_should_not_delete_when_file_reference_is_not_deletable_on_update($fileReference, $metadata)
    {
        $metadata->isDeletable(true)->willReturn(false);
        $this->delete($fileReference, true)->shouldBe(false);
    }

    function it_should_dispatch_events_when_deleting($fileReference, IEventDispatcher $dispatcher)
    {
        $dispatcher->dispatch(
            IUploadEvent::PRE_REMOVE, 
            Argument::any(), 
            Argument::any(),
            Argument::any()
        )->shouldBeCalled();
        
        $dispatcher->dispatch(
            IUploadEvent::POST_REMOVE, 
            Argument::any(), 
            Argument::any(),
            Argument::any()
        )->shouldBeCalled();
        
        $this->delete($fileReference)->shouldBe(true);
    }

    function it_should_delete_file_from_storage($fileReference, $propertyHandler)
    {
        $propertyHandler->getFile($fileReference, Argument::any())->willReturn('relative/file/path/on/storage');
        $this->delete($fileReference)->shouldBe(true);
    }

    function it_should_delete_file_from_instance_of_splFileInfo($fileReference, $fileInfo)
    {
        $this->delete($fileReference)->shouldBe(true);
        Test::assertFalse(file_exists($fileInfo->getWrappedObject()->getRealPath()));
    }

    function it_should_not_have_uploaded_file_when_file_reference_has_no_uploaded_file($fileReference, $propertyHandler)
    {
        $propertyHandler->getFile($fileReference, Argument::any())->willReturn('/it/is/not/instance/of/SplFileInfo');
        $this->shouldNotHaveUploadedFile($fileReference);
    }

    function it_should_have_uploaded_file($fileReference)
    {
        $this->shouldHaveUploadedFile($fileReference);
    }

    function it_should_throw_exception_when_moving_a_file($fileReference, $storage, $fileInfo)
    {
        $storage->writeStream($this->fsPrefix, Argument::type('string'), Argument::type('resource'))->willReturn(false);
        $this->shouldThrow(FileCouldNotBeMovedException::class)->duringMove($fileReference);
        Test::assertTrue(file_exists($fileInfo->getWrappedObject()->getRealPath()));
    }

    function it_should_move_a_file($fileReference, $propertyHandler, $storage)
    {
        $storage->writeStream($this->fsPrefix, Argument::type('string'), Argument::any())->shouldBeCalled();
        $propertyHandler->setFile($fileReference, Argument::any(), Argument::type('string'))->shouldBeCalled();
        $this->move($fileReference);
    }

    function it_should_dispatch_events_when_moving($fileReference, $dispatcher)
    {
        $dispatcher->dispatch(
            IUploadEvent::PRE_UPLOAD,
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->shouldBeCalled();

        $dispatcher->dispatch(
            IUploadEvent::POST_UPLOAD,
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->shouldBeCalled();

        $this->move($fileReference);
    }
}