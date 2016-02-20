<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Model;


trait Uploadable
{
    /**
     * @var \SplFileInfo|string|null
     */
    protected $file;

    /**
     * @var string|null
     */
    protected $uri;

    /**
     * @var \SplFileInfo|null
     */
    protected $fileInfo;

    public function __construct(\SplFileInfo $file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    public function setFileInfo(\SplFileInfo $fileInfo)
    {
        $this->fileInfo = $fileInfo;
    }

    public function toArray()
    {
        return [
            'file' => (string)$this->file,
            'uri' => (string)$this->uri,
            'fileInfo' => $this->fileInfo ? $this->fileInfo->getRealPath() : null
        ];
    }

    public function __toString()
    {
        return (string)$this->file;
    }
}