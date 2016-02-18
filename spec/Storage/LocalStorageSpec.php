<?php


namespace spec\Atom\Uploader\Storage;

use Atom\Uploader\Exception\FileNotFoundException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\Atom\Uploader\Util\FilesystemHelper;
use PHPUnit_Framework_TestCase as Test;

class LocalStorageSpec extends ObjectBehavior
{
    use FilesystemHelper;

    const PATH = 'path/to/file';

    private $fsPrefix;

    function __construct() {
        $this->fsPrefix = self::virtualPath(uniqid());
    }

    function let() {
        self::createFile(self::joinPath($this->fsPrefix, self::PATH));
    }

    function letGo() {
        $filePath = self::joinPath($this->fsPrefix, self::PATH);

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    function it_should_not_write_file_when_could_not_create_file() {
        $location = self::joinPath($this->fsPrefix, self::PATH);
        @unlink($location);
        @chmod(dirname($location), 0400);
        $this->writeStream($this->fsPrefix, self::PATH, tmpfile())->shouldBe(false);
    }

    function it_should_write_stream() {
        $fileName = uniqid('directory/');
        $this->writeStream($this->fsPrefix, $fileName, tmpfile())->shouldBe(true);
        $filePath = self::joinPath($this->fsPrefix, $fileName);
        Test::assertTrue(file_exists($filePath));
    }

    function it_should_delete_file() {
        $this->delete($this->fsPrefix, self::PATH);
        Test::assertFalse(file_exists(self::joinPath($this->fsPrefix, self::PATH)));
    }

    function it_should_not_delete_when_file_not_found() {
        $this->delete($this->fsPrefix, 'not/existen/file')->shouldBe(false);
    }

    function it_should_not_delete_when_path_is_empty($fs, $handler) {
        $this->delete($this->fsPrefix, '')->shouldBe(false);
    }

    function it_should_resolve_file_info() {
        $this->resolveFileInfo($this->fsPrefix, self::PATH)->shouldBeAnInstanceOf(\SplFileInfo::class);
    }

    function it_should_not_resolve_file_info_when_path_is_empty($fs) {
        $this->resolveFileInfo($this->fsPrefix, '')->shouldBe(null);
    }

    function it_should_not_resolve_file_info_when_path_is_not_file() {
        $this->resolveFileInfo($this->fsPrefix, 'non/existen/file')->shouldBe(null);
    }
}