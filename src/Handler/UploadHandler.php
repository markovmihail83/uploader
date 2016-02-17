<?php

namespace Atom\Uploader\Handler;

use Atom\Uploader\DependencyInjection\IContainer;
use Atom\Uploader\Event\IUploadEvent;
use Atom\Uploader\Exception\FileCouldNotBeMovedException;
use Atom\Uploader\Metadata\MetadataFactory;
use Atom\Uploader\Metadata\FileMetadata;
use Atom\Uploader\Naming\NamerFactory;
use Atom\Uploader\Event\IEventDispatcher;

class UploadHandler
{
    private $metadataFactory;

    private $propertyHandler;

    private $storageFactory;

    private $namingFactory;

    private $dispatcher;

    private $container;

    public function __construct(
        MetadataFactory $metadataFactory,
        IPropertyHandler $propertyHandler,
        IContainer $container,
        NamerFactory $namingFactory,
        IEventDispatcher $dispatcher
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->propertyHandler = $propertyHandler;
        $this->namingFactory = $namingFactory;
        $this->dispatcher = $dispatcher;
        $this->container = $container;
    }

    public function move($fileReference, $onUpdate = false) {
        $metadata = $this->metadataFactory->getMetadata($fileReference);
        $file = $this->propertyHandler->getFile($fileReference, $metadata);
        $fileName = $this->namingFactory->getNamer($metadata->getNamingStrategy())->name($file);
        $event = $this->dispatcher->dispatch(IUploadEvent::PRE_UPLOAD, $fileReference, $metadata, $onUpdate);

        if ($event->isActionStopped()) {
            return;
        }

        $this->moveUploadedFile($file, $fileName, $metadata);
        $this->updateFile($fileReference, $fileName, $metadata);
        $this->dispatcher->dispatch(IUploadEvent::POST_UPLOAD, $fileReference, $metadata, $onUpdate);
    }

    public function delete($fileReference, $onUpdate = false) {
        $metadata = $this->metadataFactory->getMetadata($fileReference);
        $file = $this->propertyHandler->getFile($fileReference, $metadata);

        if (empty($file) || !$metadata->isDeletable($onUpdate)) {
            return false;
        }

        $event = $this->dispatcher->dispatch(IUploadEvent::PRE_REMOVE, $fileReference, $metadata, $onUpdate);

        if ($event->isActionStopped()) {
            return false;
        }

        $isDeleted = $this->deleteFile($metadata, $file);

        if ($isDeleted) {
            $this->propertyHandler->setFile($fileReference, $metadata, null);
            $this->dispatcher->dispatch(IUploadEvent::POST_REMOVE, $fileReference, $metadata, $onUpdate);
        }

        return $isDeleted;
    }

    public function hasUploadedFile($fileReference) {
        $metadata = $this->metadataFactory->getMetadata($fileReference);
        $file = $this->propertyHandler->getFile($fileReference, $metadata);

        return $file instanceof \SplFileInfo;
    }

    public function isEqualFiles($fileReference1, $fileReference2) {
        if (!$fileReference1 instanceof $fileReference2) {
            return false;
        }

        $metadata = $this->metadataFactory->getMetadata($fileReference1);
        $filePath1 = (string)$this->propertyHandler->getFile($fileReference1, $metadata);
        $filePath2 = (string)$this->propertyHandler->getFile($fileReference2, $metadata);

        return $filePath1 === $filePath2;
    }

    public function injectUri($fileReference) {
        $metadata = $this->metadataFactory->getMetadata($fileReference);
        $path = (string)$this->propertyHandler->getFile($fileReference, $metadata);
        $path = ltrim($path, '\\/');
        $uriPrefix = (string)$metadata->getUriPrefix();

        if (empty($path) || false === strpos($uriPrefix, '%s')) {
            return;
        }

        $uri = str_replace('/', DIRECTORY_SEPARATOR, sprintf($uriPrefix, $path));

        $event = $this->dispatcher->dispatch(IUploadEvent::PRE_INJECT_URI, $fileReference, $metadata);

        if ($event->isActionStopped()) {
            return;
        }

        $this->propertyHandler->setUri($fileReference, $metadata, $uri);
        $this->dispatcher->dispatch(IUploadEvent::POST_INJECT_URI, $fileReference, $metadata);
    }

    public function injectFileInfo($fileReference) {
        $metadata = $this->metadataFactory->getMetadata($fileReference);
        $path = (string)$this->propertyHandler->getFile($fileReference, $metadata);

        if (!$metadata->isInjectableFileInfo() || empty($path)) {
            return;
        }

        $storage = $this->getStorageFactory()->getStorage($metadata->getStorageType());
        $fileInfo = $storage->resolveFileInfo($metadata->getFilesystemPrefix(), $path);

        if (null === $fileInfo) {
            return;
        }

        $event = $this->dispatcher->dispatch(IUploadEvent::PRE_INJECT_FILE_INFO, $fileReference, $metadata);

        if ($event->isActionStopped()) {
            return;
        }

        $this->propertyHandler->setFileInfo($fileReference, $metadata, $fileInfo);
        $this->dispatcher->dispatch(IUploadEvent::POST_INJECT_FILE_INFO, $fileReference, $metadata);
    }

    private function moveUploadedFile(\SplFileInfo $file, $fileName, FileMetadata $metadata) {
        $storage = $this->getStorageFactory()->getStorage($metadata->getStorageType());
        $stream = fopen($file->getRealPath(), 'r+');
        $isMoved = $storage->writeStream($metadata->getFilesystemPrefix(), $fileName, $stream);

        if (!$isMoved) {
            throw new FileCouldNotBeMovedException($file->getRealPath(), $fileName);
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        if ($file->isWritable()) {
            $this->deleteFileInfo($file);
        }
    }

    private function updateFile($fileReference, $fileName, FileMetadata $metadata) {
        $this->propertyHandler->setFile($fileReference, $metadata, $fileName);
        $this->injectUri($fileReference);
        $this->injectFileInfo($fileReference);
    }

    private function deleteFile(FileMetadata $metadata, $file) {
        if ($file instanceof \SplFileInfo) {
            return $this->deleteFileInfo($file);
        }

        $storage = $this->getStorageFactory()->getStorage($metadata->getStorageType());

        return $storage->delete($metadata->getFilesystemPrefix(), $file);
    }

    private function getStorageFactory() {
        return $this->storageFactory ?: $this->storageFactory = $this->container->getStorageFactory();
    }

    private function deleteFileInfo(\SplFileInfo $fileInfo) {
        return unlink($fileInfo->getRealPath());
    }
}