<?php

namespace spec\Atom\Uploader\Naming;

use Atom\Uploader\Naming\INamer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UniqueNamerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldImplement(INamer::class);
    }

    function it_should_generate_an_unique_filename(\SplFileInfo $file)
    {
        $file->getExtension()->willReturn('txt');
        $this->name($file)->shouldBeString();
    }
}
