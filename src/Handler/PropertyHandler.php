<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Handler;

use Atom\Uploader\Metadata\FileMetadata;

class PropertyHandler
{
    /**
     * @param object $fileReference
     * @param FileMetadata $metadata
     *
     * @return \SplFileInfo|string|null
     */
    public function getFile($fileReference, FileMetadata $metadata)
    {
        return $this->getValue($fileReference, $metadata->getFileGetter());
    }

    /**
     * @param object $fileReference
     * @param FileMetadata $metadata
     * @param string|null $value
     */
    public function setFile(&$fileReference, FileMetadata $metadata, $value)
    {
        $this->setValue($fileReference, $metadata->getFileSetter(), $value);
    }

    /**
     * @param object $object
     * @param string $property
     *
     * @return mixed
     */
    private function getValue($object, $property)
    {
        if (is_array($object)) {
            if (!isset($object[$property]) && 0 === strpos($property, 'get')) {
                $property = lcfirst(substr($property, 3));

            }

            return isset($object[$property]) ? $object[$property] : null;
        }

        $getter = 0 === strpos($property, 'get') ? $property : 'get' . ucfirst($property);

        if (method_exists($object, $getter)) {
            return call_user_func([$object, $getter]);
        }

        $reflection = new \ReflectionProperty(get_class($object), $property);
        $reflection->setAccessible(true);

        return $reflection->getValue($object);
    }

    /**
     * @param object $object
     * @param string $property
     * @param mixed $value
     */
    private function setValue(&$object, $property, $value)
    {
        if (is_array($object)) {
            $object[$property] = $value;
            return;
        }

        $setter = 0 === strpos($property, 'set') ? $property : 'set' . ucfirst($property);

        if (method_exists($object, $setter)) {
            call_user_func([$object, $setter], $value);

            return;
        }

        $reflection = new \ReflectionProperty(get_class($object), $property);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);
    }

    /**
     * @param object $fileReference
     * @param FileMetadata $metadata
     * @param \SplFileInfo|null $value
     */
    public function setFileInfo(&$fileReference, FileMetadata $metadata, $value)
    {
        $this->setValue($fileReference, $metadata->getFileInfoSetter(), $value);
    }

    /**
     * @param object $fileReference
     * @param FileMetadata $metadata
     * @param string|null $value
     */
    public function setUri(&$fileReference, FileMetadata $metadata, $value)
    {
        $this->setValue($fileReference, $metadata->getUriSetter(), $value);
    }
}
