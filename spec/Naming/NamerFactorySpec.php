<?php


namespace spec\Atom\Uploader\Naming;

use Atom\Uploader\Exception\NoSuchNamingException;
use Atom\Uploader\Naming\INamer;
use Atom\Uploader\Naming\NamerFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin NamerFactory
 */
class NamerFactorySpec extends ObjectBehavior
{
    function let(INamer $namer)
    {
        $this->addNamer('my_namer_strategy', $namer);
    }

    function it_should_throw_exception_when_getting_a_namer()
    {
        $this->shouldThrow(NoSuchNamingException::class)->duringGetNamer('it_is_not_registered_namer');
    }

    function it_should_get_namer($namer)
    {
        $this->getNamer('my_namer_strategy')->shouldBe($namer);
    }
}