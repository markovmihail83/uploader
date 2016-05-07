<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Handler;

use Atom\Uploader\Event\IEventDispatcher;
use Atom\Uploader\Event\IUploadEvent;
use Atom\Uploader\Exception\FileCouldNotBeMovedException;
use Atom\Uploader\LazyLoad\IFilesystemAdapterRepoLazyLoader;
use Atom\Uploader\Metadata\FileMetadata;
use Atom\Uploader\Metadata\MetadataRepo;
use Atom\Uploader\Naming\NamerRepo;

class UploadHandler
{
    private $metadataRepo;

    private $propertyHandler;

    private $fsAdapterRepo;

    private $namerRepo;

    private $dispatcher;

    private $fsAdapterLoader;

    public function __construct(
        MetadataRepo $metadataRepo,
        PropertyHandler $propertyHandler,
        IFilesystemAdapterRepoLazyLoader $fsAdapterLoader,
        NamerRepo $namerRepo,
        IEventDispatcher $dispatcher
    )
    {
        $this->metadataRepo = $metadataRepo;
        $this->propertyHandler = $propertyHandler;
        $this->namerRepo = $namerRepo;
        $this->dispatcher = $dispatcher;
        $this->fsAdapterLoader = $fsAdapterLoader;
    }

    public function upload(&$fileReference, $metadataName = null)
    {
        $this->move($fileReference, $metadataName, IUploadEvent::PRE_UPLOAD, IUploadEvent::POST_UPLOAD);
    }

    public function injectUri(&$fileReference, $metadataName = null)
    {
        $metadata = $this->metadataRepo->getMetadata($metadataName ?: $fileReference);

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

    public function injectFileInfo(&$fileReference, $metadataName = null)
    {
        $metadata = $this->metadataRepo->getMetadata($metadataName ?: $fileReference);
        $path = (string)$this->propertyHandler->getFile($fileReference, $metadata);

        if (!$metadata->isInjectableFileInfo() || empty($path)) {
            return;
        }

        $filesystem = $this->getFilesystemAdapterRepo()->getFilesystem($metadata->getFsAdapter());
        $fileInfo = $filesystem->resolveFileInfo($metadata->getFsPrefix(), $path);

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

    public function update(&$fileReference, $metadataName = null)
    {
        $this->move($fileReference, $metadataName, IUploadEvent::PRE_UPDATE, IUploadEvent::POST_UPDATE);
    }

    public function deleteOldFile($fileReference, $metadataName = null)
    {
        $metadata = $this->metadataRepo->getMetadata($metadataName ?: $fileReference);
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

    public function delete(&$fileReference, $metadataName = null)
    {
        $metadata = $this->metadataRepo->getMetadata($metadataName ?: $fileReference);
        $file = $this->propertyHandler->getFile($fileReference, $metadata);

        if (empty($file) || !$metadata->isDeletable()) {
            return false;
        }

        return $this->remove($fileReference, $metadata, $file, IUploadEvent::PRE_REMOVE, IUploadEvent::POST_REMOVE);
    }

    public function isFilesEqual($fileReference1, $fileReference2, $metadataName = null)
    {
        $metadata = $this->metadataRepo->getMetadata($metadataName ?: $fileReference1);
        $filePath1 = (string)$this->propertyHandler->getFile($fileReference1, $metadata);
        $filePath2 = (string)$this->propertyHandler->getFile($fileReference2, $metadata);

        return $filePath1 === $filePath2;
    }

    public function hasUploadedFile($fileReference, $metadataName = null)
    {
        $metadata = $this->metadataRepo->getMetadata($metadataName ?: $fileReference);
        $file = $this->propertyHandler->getFile($fileReference, $metadata);

        return $file instanceof \SplFileInfo;
    }

    public function isFileReference($fileReference, $metadataName = null)
    {
        return $this->metadataRepo->hasMetadata($metadataName ?: $fileReference);
    }

    private function moveUploadedFile(\SplFileInfo $file, $fileName, FileMetadata $metadata)
    {
        $filesystem = $this->getFilesystemAdapterRepo()->getFilesystem($metadata->getFsAdapter());
        $stream = fopen((string)$file, 'r');
        $isMoved = $filesystem->writeStream($metadata->getFsPrefix(), $fileName, $stream);

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

        $filesystem = $this->getFilesystemAdapterRepo()->getFilesystem($metadata->getFsAdapter());

        return $filesystem->delete($metadata->getFsPrefix(), $file);
    }

    private function remove(&$fileReference, $metadata, $file, $preEventName, $postEventName)
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

    private function move(&$fileReference, $metadataName, $preEventName, $postEventName)
    {
        $metadata = $this->metadataRepo->getMetadata($metadataName ?: $fileReference);
        $file = $this->propertyHandler->getFile($fileReference, $metadata);
        $fileName = $this->namerRepo->getNamer($metadata->getNamingStrategy())->name($file);
        $event = $this->dispatcher->dispatch($preEventName, $fileReference, $metadata);

        if ($event->isActionStopped()) {
            return;
        }

        $this->moveUploadedFile($file, $fileName, $metadata);
        $this->propertyHandler->setFile($fileReference, $metadata, $fileName);
        $this->injectUri($fileReference, $metadataName);
        $this->injectFileInfo($fileReference, $metadataName);
        $this->dispatcher->dispatch($postEventName, $fileReference, $metadata);
    }

    private function getFilesystemAdapterRepo()
    {
        return $this->fsAdapterRepo ?: $this->fsAdapterRepo = $this->fsAdapterLoader->getFilesystemAdapterRepo();
    }
}
