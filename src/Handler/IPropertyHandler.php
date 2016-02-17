<?php


namespace Atom\Uploader\Handler;


use Atom\Uploader\Metadata\FileMetadata;

interface IPropertyHandler
{
    /**
     * @param object       $fileReference
     * @param FileMetadata $metadata
     *
     * @return \SplFileInfo|string|null
     */
    public function getFile($fileReference, FileMetadata $metadata);

    /**
     * @param object       $fileReference
     * @param FileMetadata $metadata
     * @param string|null  $value
     */
    public function setFile($fileReference, FileMetadata $metadata, $value);


    /**
     * @param object            $fileReference
     * @param FileMetadata      $metadata
     * @param \SplFileInfo|null $value
     */
    public function setFileInfo($fileReference, FileMetadata $metadata, $value);


    /**
     * @param object       $fileReference
     * @param FileMetadata $metadata
     * @param string|null  $value
     */
    public function setUri($fileReference, FileMetadata $metadata, $value);
}