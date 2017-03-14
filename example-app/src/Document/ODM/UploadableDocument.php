<?php
/**
 * Copyright © 2017 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Document\ODM;

class UploadableDocument
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

    /**
     * @var int
     */
    private $id;

    public function __construct(\SplFileInfo $file)
    {
        $this->file = $file;
    }

    public function getId()
    {
        return $this->id;
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
            'file' => $this->file ? (string)$this->file : null,
            'uri' => $this->uri ? (string)$this->uri : null,
            'fileInfo' => $this->fileInfo ? (string)$this->fileInfo : null,
        ];
    }

    public function __toString()
    {
        return (string)$this->file;
    }
}
