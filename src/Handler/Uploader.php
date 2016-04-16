<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Handler;

use Atom\Uploader\LazyLoad\IUploadHandlerLazyLoader;

/**
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Uploader
{
    private $uploadHandlerLoader;

    private $uploadedFiles;

    private $oldFiles;

    private $uploadHandler;

    public function __construct(IUploadHandlerLazyLoader $uploadHandlerLoader)
    {
        $this->uploadHandlerLoader = $uploadHandlerLoader;
        $this->uploadedFiles = [];
        $this->oldFiles = [];
    }

    public function persist($id, &$fileReference = null, $metadataName = null)
    {
        if (!$this->hasUploadedFile($fileReference, $metadataName)) {
            return;
        }

        $this->getUploadHandler()->upload($fileReference, $metadataName);
        $this->uploadedFiles[$id] = [$fileReference, $metadataName];
    }

    public function saved($id)
    {
        $this->detachUploadedFile($id);
    }

    public function update($id, &$newFileReference = null, $oldFileReference = null, $metadataName = null)
    {
        if ($this->hasUploadedFile($newFileReference, $metadataName)) {
            $this->getUploadHandler()->update($newFileReference, $metadataName);
            $this->uploadedFiles[$id] = [$newFileReference, $metadataName];
        }

        if ($oldFileReference && (!$newFileReference || !$this->getUploadHandler()->isFilesEqual($newFileReference, $oldFileReference, $metadataName))) {
            $this->oldFiles[$id] = [$oldFileReference, $metadataName ?: get_class($newFileReference)];
        }
    }

    public function updated($id)
    {
        $this->detachUploadedFile($id);
        $this->deleteOldFile($id);
    }

    public function loaded(&$fileReference = null, $metadataName = null)
    {
        if (!$this->isFileReference($fileReference, $metadataName)) {
            return;
        }

        $uploadHandler = $this->getUploadHandler();

        $uploadHandler->injectUri($fileReference, $metadataName);
        $uploadHandler->injectFileInfo($fileReference, $metadataName);
    }

    public function removed(&$fileReference = null, $metadataName = null)
    {
        if (!$this->isFileReference($fileReference, $metadataName)) {
            return;
        }

        $this->getUploadHandler()->delete($fileReference, $metadataName);
    }

    public function flush()
    {
        if (empty($this->uploadedFiles)) {
            return;
        }

        $uploadHandler = $this->getUploadHandler();

        foreach ($this->uploadedFiles as $file) {
            list ($fileReference, $metadataName) = $file;
            $uploadHandler->delete($fileReference, $metadataName);
        }
    }

    private function hasUploadedFile($fileReference, $metadataName)
    {
        return $this->isFileReference($fileReference, $metadataName) && $this->getUploadHandler()->hasUploadedFile($fileReference, $metadataName);
    }

    private function deleteOldFile($id)
    {
        if (isset($this->oldFiles[$id])) {
            list ($fileReference, $metadataName) = $this->oldFiles[$id];
            $this->getUploadHandler()->deleteOldFile($fileReference, $metadataName);
            unset($this->oldFiles[$id]);
        }
    }

    private function detachUploadedFile($id)
    {
        if (isset($this->uploadedFiles[$id])) {
            unset($this->uploadedFiles[$id]);
        }
    }

    private function isFileReference($fileReference, $metadataName)
    {
        return $fileReference && $this->getUploadHandler()->isFileReference($fileReference, $metadataName);
    }

    private function getUploadHandler()
    {
        return $this->uploadHandler ?: $this->uploadHandler = $this->uploadHandlerLoader->getUploadHandler();
    }
}
