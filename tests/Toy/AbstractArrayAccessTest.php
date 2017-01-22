<?php

namespace Toy;

class AbstractArrayAccessTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AbstractArrayAccess
     */
    protected $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass(AbstractArrayAccess::class);
    }

    public function testOffsetExists()
    {
        $this->assertFalse($this->obj->offsetExists(1));
    }

    public function testOffsetUnset()
    {
        $this->assertNull($this->obj->offsetUnset(1));
    }

}
