<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace spec\Atom\Uploader\Naming;


use Atom\Uploader\Naming\BasenameNamer;
use Atom\Uploader\Naming\INamer;
use PhpSpec\ObjectBehavior;

/**
 * @mixin BasenameNamer
 */
class BasenameNamerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldImplement(INamer::class);
    }

    function it_should_generate_a_filename_from_basename(\SplFileInfo $file)
    {
        $basename = 'some-filename.ext';
        $file->getBasename()->willReturn($basename);
        $this->name($file)->shouldBe($basename);
    }

}