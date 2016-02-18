<?php


namespace spec\Atom\Uploader\Storage;

use Atom\Uploader\Exception\NoSuchStorageException;
use Atom\Uploader\Storage\IStorage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StorageFactorySpec extends ObjectBehavior
{
    function let(IStorage $storage)
    {
        $this->addStorage('my_storage', $storage);
    }

    function it_should_get_storage($storage)
    {
        $this->getStorage('my_storage')->shouldBe($storage);
    }

    function it_should_throw_exception_when_getting_a_storage()
    {
        $this->shouldThrow(NoSuchStorageException::class)->duringGetStorage('non_existen_storage');
    }
}