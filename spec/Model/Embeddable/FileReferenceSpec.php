<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace spec\Atom\Uploader\Model\Embeddable;

use Atom\Uploader\Model\Embeddable\FileReference;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin FileReference
 */
class FileReferenceSpec extends ObjectBehavior
{
    const REAL_PATH = '/fully/qualified/path/to/file';

    function let(\SplFileInfo $file)
    {
        $this->beConstructedWith($file);

        $file->getRealPath()->willReturn(self::REAL_PATH);
        $file->__toString()->willReturn(self::REAL_PATH);
    }

    function it_should_get_a_file_as_string()
    {
        $this->__toString()->shouldBe(self::REAL_PATH);
    }

    function it_should_set_an_uri()
    {
        $this->setUri('uri');
        $this->getUri()->shouldBe('uri');
    }

    function it_should_get_a_file($file)
    {
        $this->getFile()->shouldBe($file);
    }

    function it_should_set_a_file()
    {
        $this->setFile('file');
        $this->getFile()->shouldBe('file');
    }

    function it_should_set_a_file_info(\SplFileInfo $fileInfo)
    {
        $this->setFileInfo($fileInfo);
        $this->getFileInfo()->shouldBe($fileInfo);
    }

    function it_should_convert_self_to_an_array(\SplFileInfo $fileInfo)
    {
        $this->setUri('/uri');
        $fileInfo->getRealPath()->willReturn(self::REAL_PATH);
        $this->setFileInfo($fileInfo);
        $this->toArray()->shouldHaveKeyWithValue('uri', '/uri');
        $this->toArray()->shouldHaveKeyWithValue('file', self::REAL_PATH);
        $this->toArray()->shouldHaveKeyWithValue('fileInfo', self::REAL_PATH);
    }
}