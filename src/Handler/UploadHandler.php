<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

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

    private $namerFactory;

    private $dispatcher;

    private $container;

    public function __construct(
        MetadataFactory $metadataFactory,
        IPropertyHandler $propertyHandler,
        IContainer $container,
        NamerFactory $namerFactory,
        IEventDispatcher $dispatcher
    )
    {
        $this->metadataFactory = $metadataFactory;
        $this->propertyHandler = $propertyHandler;
        $this->namerFactory = $namerFactory;
        $this->dispatcher = $dispatcher;
        $this->container = $container;
    }

    public function upload($fileReference)
    {
        $this->move($fileReference, IUploadEvent::PRE_UPLOAD, IUploadEvent::POST_UPLOAD);
    }

    public function update($fileReference)
    {
        $this->move($fileReference, IUploadEvent::PRE_UPDATE, IUploadEvent::POST_UPDATE);
    }

    public function deleteOldFile($fileReference)
    {
        $metadata = $this->metadataFactory->getMetadata($fileReference);
        $file = $this->propertyHandler->getFile($fileReference, $metadata);

        if (empty($file) || !$metadata->isOldFileDeletable()) {
            return false;
        }

        return $this->remove(
            $fileReference,
            $metadata,
            $file,
            IUploadEvent::PRE_REMOVE_OLD_FILE,
            IUploadEvent::POST_REMOVE_OLD_FILE
        );
    }

    public function delete($fileReference)
    {
        $metadata = $this->metadataFactory->getMetadata($fileReference);
        $file = $this->propertyHandler->getFile($fileReference, $metadata);

        if (empty($file) || !$metadata->isDeletable()) {
            return false;
        }

        return $this->remove($fileReference, $metadata, $file, IUploadEvent::PRE_REMOVE, IUploadEvent::POST_REMOVE);
    }

    public function isFilesEqual($fileReference1, $fileReference2)
    {
        $metadata = $this->metadataFactory->getMetadata($fileReference1);
        $filePath1 = (string)$this->propertyHandler->getFile($fileReference1, $metadata);
        $filePath2 = (string)$this->propertyHandler->getFile($fileReference2, $metadata);

        return $filePath1 === $filePath2;
    }

    public function hasUploadedFile($fileReference)
    {
        $metadata = $this->metadataFactory->getMetadata($fileReference);
        $file = $this->propertyHandler->getFile($fileReference, $metadata);

        return $file instanceof \SplFileInfo;
    }

    public function injectUri($fileReference)
    {
        $metadata = $this->metadataFactory->getMetadata($fileReference);

        if (!$metadata->isInjectableUri() || false === $metadata->getUriSetter()) {
            return;
        }

        $path = (string)$this->propertyHandler->getFile($fileReference, $metadata);
        $path = ltrim($path, '\\/');
        $uriPrefix = (string)$metadata->getUriPrefix();

        if (empty($path) || false === strpos($uriPrefix, '%s')) {
            return;
        }

        $uri = sprintf($uriPrefix, $path);

        $event = $this->dispatcher->dispatch(IUploadEvent::PRE_INJECT_URI, $fileReference, $metadata);

        if ($event->isActionStopped()) {
            return;
        }

        $this->propertyHandler->setUri($fileReference, $metadata, $uri);
        $this->dispatcher->dispatch(IUploadEvent::POST_INJECT_URI, $fileReference, $metadata);
    }

    public function injectFileInfo($fileReference)
    {
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

    private function remove($fileReference, $metadata, $file, $preEventName, $postEventName)
    {
        $event = $this->dispatcher->dispatch($preEventName, $fileReference, $metadata);

        if ($event->isActionStopped()) {
            return false;
        }

        $isDeleted = $this->deleteFile($metadata, $file);

        if ($isDeleted) {
            $this->propertyHandler->setFile($fileReference, $metadata, null);
            $this->dispatcher->dispatch($postEventName, $fileReference, $metadata);
        }

        return $isDeleted;
    }

    private function move($fileReference, $preEventName, $postEventName)
    {
        $metadata = $this->metadataFactory->getMetadata($fileReference);
        $file = $this->propertyHandler->getFile($fileReference, $metadata);
        $fileName = $this->namerFactory->getNamer($metadata->getNamingStrategy())->name($file);
        $event = $this->dispatcher->dispatch($preEventName, $fileReference, $metadata);

        if ($event->isActionStopped()) {
            return;
        }

        $this->moveUploadedFile($file, $fileName, $metadata);
        $this->propertyHandler->setFile($fileReference, $metadata, $fileName);
        $this->injectUri($fileReference);
        $this->injectFileInfo($fileReference);
        $this->dispatcher->dispatch($postEventName, $fileReference, $metadata);
    }

    private function moveUploadedFile(\SplFileInfo $file, $fileName, FileMetadata $metadata)
    {
        $storage = $this->getStorageFactory()->getStorage($metadata->getStorageType());
        $stream = fopen((string)$file, 'r+');
        $isMoved = $storage->writeStream($metadata->getFilesystemPrefix(), $fileName, $stream);

        if (!$isMoved) {
            throw new FileCouldNotBeMovedException((string)$file, $fileName);
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        if ($file->isWritable()) {
            unlink((string)$file);
        }
    }

    private function deleteFile(FileMetadata $metadata, $file)
    {
        if ($file instanceof \SplFileInfo) {
            return unlink((string)$file);
        }

        $storage = $this->getStorageFactory()->getStorage($metadata->getStorageType());

        return $storage->delete($metadata->getFilesystemPrefix(), $file);
    }

    private function getStorageFactory()
    {
        return $this->storageFactory ?: $this->storageFactory = $this->container->getStorageFactory();
    }
}