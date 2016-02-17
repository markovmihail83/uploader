<?php


namespace Atom\Uploader\Handler;


use Atom\Uploader\DependencyInjection\IContainer;

class ListenerHandler
{
    private $container;

    private $uploadedFiles;

    private $movedFiles;

    private $uploadHandler;

    public function __construct(IContainer $container) {
        $this->container = $container;
        $this->uploadedFiles = [];
        $this->movedFiles = [];
    }

    private function getUploadHandler() {
        return $this->uploadHandler ?: $this->uploadHandler = $this->container->getUploadHandler();
    }

    public function prePersist($id, $fileReference) {
        if (!$this->hasUploadedFile($fileReference)) {
            return;
        }

        $this->getUploadHandler()->move($fileReference);
        $this->uploadedFiles[$id] = $fileReference;
    }

    public function postPersist($id) {
        $this->detachUploadedFile($id);
    }

    public function preUpdate($id, $fileReference, $oldFileReference) {
        if (!$this->hasUploadedFile($fileReference)) {
            return;
        }

        $uploadHandler = $this->getUploadHandler();

        $uploadHandler->move($fileReference, true);
        $this->uploadedFiles[$id] = $fileReference;

        if (!$uploadHandler->isEqualFiles($fileReference, $oldFileReference)) {
            $this->movedFiles[$id] = $oldFileReference;
        }
    }

    public function postUpdate($id) {
        $this->detachUploadedFile($id);
        $this->deleteMovedFile($id);
    }

    public function postLoad($fileReference) {
        if (!$fileReference) {
            return;
        }

        $uploadHandler = $this->getUploadHandler();

        $uploadHandler->injectUri($fileReference);
        $uploadHandler->injectFileInfo($fileReference);
    }

    public function postRemove($fileReference) {
        if (!$fileReference) {
            return;
        }

        $this->getUploadHandler()->delete($fileReference);
    }

    public function postFlush() {
        if (empty($this->uploadedFiles)) {
            return;
        }

        $uploadHandler = $this->getUploadHandler();

        foreach ($this->uploadedFiles as $fileReference) {
            $uploadHandler->delete($fileReference);
        }
    }

    private function deleteMovedFile($id) {
        if (isset($this->movedFiles[$id])) {
            $this->getUploadHandler()->delete($this->movedFiles[$id], true);
            unset($this->movedFiles[$id]);
        }
    }

    private function detachUploadedFile($id) {
        if (isset($this->uploadedFiles[$id])) {
            unset($this->uploadedFiles[$id]);
        }
    }

    private function hasUploadedFile($fileReference) {
        return $fileReference && $this->getUploadHandler()->hasUploadedFile($fileReference);
    }

}