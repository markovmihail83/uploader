<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Handler;


use Atom\Uploader\LazyLoad\IUploadHandlerLazyLoader;

class EventHandler
{
    private $uploadHandlerLazyLoader;

    private $uploadedFiles;

    private $oldFiles;

    private $uploadHandler;

    public function __construct(IUploadHandlerLazyLoader $uploadHandlerLazyLoader)
    {
        $this->uploadHandlerLazyLoader = $uploadHandlerLazyLoader;
        $this->uploadedFiles = [];
        $this->oldFiles = [];
    }

    public function prePersist($id, $fileReference)
    {
        if (!$this->hasUploadedFile($fileReference)) {
            return;
        }

        $this->getUploadHandler()->upload($fileReference);
        $this->uploadedFiles[$id] = $fileReference;
    }

    public function postPersist($id)
    {
        $this->detachUploadedFile($id);
    }

    public function preUpdate($id, $newFileReference, $oldFileReference)
    {
        if (!$this->hasUploadedFile($newFileReference)) {
            return;
        }

        $uploadHandler = $this->getUploadHandler();

        $uploadHandler->update($newFileReference);
        $this->uploadedFiles[$id] = $newFileReference;

        if ($oldFileReference && !$uploadHandler->isFilesEqual($newFileReference, $oldFileReference)) {
            $this->oldFiles[$id] = $oldFileReference;
        }
    }

    public function postUpdate($id)
    {
        $this->detachUploadedFile($id);
        $this->deleteOldFile($id);
    }

    public function postLoad($fileReference)
    {
        if (!$this->isFileReference($fileReference)) {
            return;
        }

        $uploadHandler = $this->getUploadHandler();

        $uploadHandler->injectUri($fileReference);
        $uploadHandler->injectFileInfo($fileReference);
    }

    public function postRemove($fileReference)
    {
        if (!$this->isFileReference($fileReference)) {
            return;
        }

        $this->getUploadHandler()->delete($fileReference);
    }

    public function postFlush()
    {
        if (empty($this->uploadedFiles)) {
            return;
        }

        $uploadHandler = $this->getUploadHandler();

        foreach ($this->uploadedFiles as $fileReference) {
            $uploadHandler->delete($fileReference);
        }
    }

    private function deleteOldFile($id)
    {
        if (isset($this->oldFiles[$id])) {
            $this->getUploadHandler()->deleteOldFile($this->oldFiles[$id]);
            unset($this->oldFiles[$id]);
        }
    }

    private function detachUploadedFile($id)
    {
        if (isset($this->uploadedFiles[$id])) {
            unset($this->uploadedFiles[$id]);
        }
    }

    private function hasUploadedFile($fileReference)
    {
        return $this->isFileReference($fileReference) && $this->getUploadHandler()->hasUploadedFile($fileReference);
    }

    private function isFileReference($fileReference)
    {
        return $fileReference && $this->getUploadHandler()->isFileReference($fileReference);
    }

    private function getUploadHandler()
    {
        return $this->uploadHandler ?: $this->uploadHandler = $this->uploadHandlerLazyLoader->getUploadHandler();
    }
}