<?php

namespace Atom\Uploader\Metadata;


class FileMetadata
{
    private $fileSetter;

    private $fileGetter;

    private $uriSetter;

    private $fileInfoSetter;

    private $filesystemPrefix;

    private $uriPrefix;

    private $storageType;

    private $namingStrategy;

    private $deleteOnUpdate;

    private $deleteOnRemove;

    private $injectUriOnLoad;

    private $injectFileInfoOnLoad;

    public function __construct(
        $fileSetter,
        $fileGetter,
        $uriSetter,
        $fileInfoSetter,
        $filesystemPrefix,
        $uriPrefix,
        $storageType,
        $namingStrategy,
        $deleteOnUpdate,
        $deleteOnRemove,
        $injectUriOnLoad,
        $injectFileInfoOnLoad
    ) {
        $this->fileSetter = $fileSetter;
        $this->fileGetter = $fileGetter;
        $this->uriSetter = $uriSetter;
        $this->fileInfoSetter = $fileInfoSetter;
        $this->filesystemPrefix = $filesystemPrefix;
        $this->uriPrefix = $uriPrefix;
        $this->storageType = $storageType;
        $this->namingStrategy = $namingStrategy;
        $this->deleteOnUpdate = $deleteOnUpdate;
        $this->deleteOnRemove = $deleteOnRemove;
        $this->injectUriOnLoad = $injectUriOnLoad;
        $this->injectFileInfoOnLoad = $injectFileInfoOnLoad;
    }

    /**
     * @return string
     */
    public function getFileInfoSetter()
    {
        return $this->fileInfoSetter;
    }

    /**
     * @return string
     */
    public function getFileSetter()
    {
        return $this->fileSetter;
    }

    /**
     * @return string
     */
    public function getFileGetter()
    {
        return $this->fileGetter;
    }

    /**
     * @return string
     */
    public function getUriSetter()
    {
        return $this->uriSetter;
    }

    /**
     * @return string
     */
    public function getFilesystemPrefix()
    {
        return $this->filesystemPrefix;
    }

    /**
     * @return string
     */
    public function getUriPrefix()
    {
        return $this->uriPrefix;
    }

    /**
     * @return string
     */
    public function getStorageType()
    {
        return $this->storageType;
    }

    /**
     * @return string
     */
    public function getNamingStrategy()
    {
        return $this->namingStrategy;
    }

    /**
     * @param bool $onUpdate
     *
     * @return bool
     */
    public function isDeletable($onUpdate = false)
    {
        return $onUpdate ? $this->deleteOnUpdate : $this->deleteOnRemove;
    }

    /**
     * @return bool
     */
    public function isInjectableUri()
    {
        return $this->injectUriOnLoad;
    }

    /**
     * @return bool
     */
    public function isInjectableFileInfo()
    {
        return $this->injectFileInfoOnLoad;
    }
}