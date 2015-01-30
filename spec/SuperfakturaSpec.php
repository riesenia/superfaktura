<?php

namespace spec\Rshop\Synchronization;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rshop\Synchronization\TestConfig;

class SuperfakturaSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(TestConfig::EMAIL, TestConfig::API_KEY);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Rshop\Synchronization\Superfaktura');
    }

    public function it_throws_exception_on_wrong_entity_name()
    {
        $this->shouldThrow('DomainException')->during('create', [Argument::any()]);
    }

    public function it_creates_existing_objects()
    {
        $this->create('Invoice')->shouldBeAnInstanceOf('Rshop\Synchronization\Superfaktura\Invoice');
    }
}
