<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

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

    private $deleteOldFile;

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
        $deleteOldFile,
        $deleteOnRemove,
        $injectUriOnLoad,
        $injectFileInfoOnLoad
    )
    {
        $this->fileSetter = $fileSetter;
        $this->fileGetter = $fileGetter;
        $this->uriSetter = $uriSetter;
        $this->fileInfoSetter = $fileInfoSetter;
        $this->filesystemPrefix = $filesystemPrefix;
        $this->uriPrefix = $uriPrefix;
        $this->storageType = $storageType;
        $this->namingStrategy = $namingStrategy;
        $this->deleteOldFile = $deleteOldFile;
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
     * @return bool
     */
    public function isDeletable()
    {
        return $this->deleteOnRemove;
    }

    /**
     * @return bool
     */
    public function isOldFileDeletable()
    {
        return $this->deleteOldFile;
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