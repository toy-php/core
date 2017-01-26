<?php

namespace Core;

class TestObserver extends AbstractObserver
{
    protected $result = [];

    protected $events = [
        'model:fetch' => 'fetch',
        'model:create' => 'save',
        'model:update' => 'save',
        'model:delete' => 'delete',
        'model:validation.error' => 'setResult',
    ];

    /**
     * @param AbstractModel $subject
     * @param $event
     * @param $options
     */
    public function fetch($subject, $event, $options)
    {
        $subject->fill(['test' => 'test']);
        $subject->setChanged(false);
        $this->setResult($subject, $event, $options);

    }

    /**
     * @param AbstractModel $subject
     * @param $event
     * @param $options
     */
    public function save($subject, $event, $options)
    {
        $subject->set('id', 1);
        $subject->setChanged(false);
        $this->setResult($subject, $event, $options);
    }

    /**
     * @param AbstractModel $subject
     * @param $event
     * @param $options
     */
    public function delete($subject, $event, $options)
    {
        $subject->clear();
        $this->setResult($subject, $event, $options);
    }

    public function setResult($subject, $event, $options)
    {
        $this->result = [$subject, $event, $options];
    }

    public function getResult()
    {
        return $this->result;
    }
}

class AbstractModelTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AbstractModel
     */
    protected $obj;

    /**
     * @var TestObserver
     */
    protected $observer;

    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass(AbstractModel::class, [
            ['test' => '', 'test2' => '', 'test3' => '']
        ]);
        $this->observer = new TestObserver();
        $this->obj->bind([
            'model:fetch',
            'model:create',
            'model:update',
            'model:delete',
            'model:validation.error'
        ], $this->observer);
    }

    public function testGetPrimaryKey()
    {
        $this->assertTrue($this->obj->getPrimaryKey() === 'id');
    }

    public function testSetChanged()
    {
        $this->obj->setChanged(true);
        $this->assertTrue($this->obj->isChanged());
    }

    public function testFill()
    {
        $this->obj->fill(['test' => 'test']);
        $this->assertTrue($this->obj->toArray() == ['test' => 'test', 'test2' => '', 'test3' => '']);
    }

    public function testValidate()
    {
        $this->assertTrue($this->obj->validate(['test' => 'test']) == ['test' => 'test']);
    }

    public function testHas()
    {
        $this->obj->fill(['test' => 'test']);
        $this->assertTrue($this->obj->has('test'));
    }

    public function testMagicIsset()
    {
        $this->obj->fill(['test' => 'test']);
        $this->assertTrue(isset($this->obj->test));
    }

    public function testGet()
    {
        $this->obj->fill(['test' => 'test']);
        $this->assertTrue($this->obj->get('test') === 'test');
    }

    public function testMagicGet()
    {
        $this->obj->fill(['test' => 'test']);
        $this->assertTrue($this->obj->test === 'test');
    }

    public function testSet()
    {
        $this->obj->set('test', 'test');
        $this->obj->set('test2', 'test2');
        $this->obj->set('test3', 'test3');
        $this->assertTrue($this->obj->toArray() === ['test' => 'test', 'test2' => 'test2', 'test3' => 'test3']);
    }

    public function testMagicSet()
    {
        $this->obj->test = 'test';
        $this->assertTrue($this->obj->get('test') === 'test');
    }

    public function testRemove()
    {
        $this->obj->set('test', 'test');
        $this->obj->remove('test');
        $this->assertFalse($this->obj->has('test'));
    }

    public function testClear()
    {
        $this->obj->set('test', 'test');
        $this->obj->clear();
        $this->assertTrue(empty($this->obj->toArray()));
    }

    public function testHasValidationError()
    {
        $this->obj->set('test', 'test');
        $this->assertFalse($this->obj->hasValidationError());
    }

    public function testIsChanged()
    {
        $this->obj->set('test', 'test');
        $this->assertTrue($this->obj->isChanged());
    }

    public function testFetch()
    {
        $this->obj->set('id', 1);
        $this->obj->fetch();
        $this->assertTrue($this->obj->has('test'));
    }

    public function testSave()
    {
        $this->obj->set('test', 'test');
        $this->obj->save();
        $this->assertTrue($this->obj->get('id') > 0);
    }

    public function testDestroy()
    {
        $this->obj->set('id', 1);
        $this->obj->destroy();
        $this->assertTrue(empty($this->obj->toArray()));
    }

    public function testToArray()
    {
        $this->obj->set('test', 'test');
        $this->assertTrue($this->obj->toArray() == ['test' => 'test', 'test2' => '', 'test3' => '']);
    }

    public function testOffsetGet()
    {
        $this->obj['test'] = function () {
            return $this->getMockForAbstractClass(AbstractModel::class, [], 'Test');
        };
        $this->assertInstanceOf(ModelInterface::class, $this->obj['test']);
    }

    public function testOffsetSet()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->obj['test'] = 'test';
    }
}
