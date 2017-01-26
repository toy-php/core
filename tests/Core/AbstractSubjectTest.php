<?php

namespace Core;

class AbstractSubjectTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AbstractSubject
     */
    protected $obj;

    /**
     * @var AbstractObserver
     */
    protected $observer;

    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass(AbstractSubject::class);
        $this->observer = $this->getMockForAbstractClass(AbstractObserver::class);
    }

    public function testBind()
    {
        $this->assertNull($this->obj->bind(['test'], $this->observer));
    }

    public function testUnbind()
    {
        $this->obj->bind(['test'], $this->observer);
        $this->assertNull($this->obj->unbind(['test'], $this->observer));
    }

    public function testTrigger()
    {
        $this->obj->bind(['test'], $this->observer);
        $this->assertNull($this->obj->trigger(['test']));
    }

}
