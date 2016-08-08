<?php

/**
 * This file is part of the Cubiche/Tests component.
 *
 * Copyright (c) Cubiche
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cubiche\Tests;

use Faker\Generator as FakerGenerator;
use mageekguy\atoum\mock\aggregator as Aggregator;
use mageekguy\atoum\test\adapter as Adapter;

/**
 * Tests trait.
 *
 * @author Karel Osorio Ramírez <osorioramirez@gmail.com>
 */
trait TestTrait
{
    use mageekguy\atoum\stubs\asserters;
    
    /**
     * @var FakerGenerator
     */
    public $faker;
    
    /**
     * @var static
     */
    public $and;
    
    /**
     * @var static
     */
    public $assert;
    
    /**
     * @var test\assertion\aliaser
     */
    public $define;
    
    /**
     * @var \exception
     */
    public $exception;
    
    /**
     * @var php\mocker
     */
    public $function;
    
    /**
     * @var static
     */
    public $given;
    
    /**
     * @var static
     */
    public $if;
    
    /**
     * @var object
     */
    public $newInstance;
    
    /**
     * @var object
     */
    public $newTestedInstance;
    
    /**
     * @var asserters\testedClass
     */
    public $testedClass;
    
    /**
     * @var object
     */
    public $testedInstance;
    
    /**
     * @var static
     */
    public $then;
    
    /**
     * @param Aggregator $mock
     *
     * @return \mageekguy\atoum\mock\controller
     */
    public function ƒ(Aggregator $mock) {}
    
    /**
     * @param string $case
     *
     * @return $this
     */
    public function assert($case = null) {}
    
    /**
     * @param Aggregator $mock
     *
     * @return \mageekguy\atoum\mock\controller
     */
    public function calling(Aggregator $mock) {}
    
    /**
     * @param mixed ...$mixed
     *
     * @return $this
     */
    public function define(...$mixed) {}
    
    /**
     * @param ...$mixed
     *
     * @return $this
     */
    public function dump(...$mixed) {}
    
    /**
     * @param mixed $mixed
     *
     * @return $this
     */
    public function dumpOnFailure($mixed) {}
    
    /**
     * @param \Closure $callback
     *
     * @return $this
     */
    public function executeOnFailure(\Closure $callback) {}
    
    /**
     * @param string $class
     *
     * @return $this
     */
    public function from($class) {}
    
    /**
     * @param mixed ...$mixed
     *
     * @return $this
     */
    public function given(...$mixed) {}
    
    /**
     * @param mixed ...$mixed
     *
     * @return $this
     */
    public function let(...$mixed) {}
    
    /**
     * @param string $class
     * @param string $mockNamespace
     * @param string $mockClass
     *
     * @return $this
     */
    public function mockClass($class, $mockNamespace = null, $mockClass = null) {}
    
    /**
     * @param mixed ...$mixed
     *
     * @return \mageekguy\atoum\mock\generator
     */
    public function mockGenerator() {}
    
    /**
     * @param string $mockNamespace
     * @param string $mockClass
     *
     * @return $this
     */
    public function mockTestedClass($mockNamespace = null, $mockClass = null) {}
    
    /**
     * @param mixed ...$arguments
     *
     * @return object
     */
    public function newTestedInstance(...$arguments) {}
    
    /**
     * @param mixed ...$arguments
     *
     * @return object
     */
    public function newInstance(...$arguments) {}
    
    /**
     * @throws \mageekguy\atoum\test\exceptions\stop
     *
     * @return $this
     */
    public function stop() {}
    
    /**
     * @param mixed ...$mixed
     *
     * @return $this
     */
    public function then(...$mixed) {}
    
    /**
     * @param \Closure|mixed $mixed
     *
     * @return $this
     */
    public function when($mixed) {}
    
    /**
     * @param Adapter $adapter
     *
     * @return \mageekguy\atoum\stubs\asserters\adapter
     */
    public function adapter(Adapter $adapter) {}
    
    /**
     * @param string $class
     * @param string $mockNamespace
     * @param string $mockClass
     * @param array  $constructorArguments
     *
     * @return object
     */
    public function newMockInstance($class, $mockNamespace = null, $mockClass = null, array $constructorArguments = null) {}
    
    /**
     * @return object
     */
    public function newDefaultTestedInstance(){}

    /**
     * @return object
     */
    public function newDefaultMockTestedInstance(){}

    /**
     * @return array
     */
    protected function defaultConstructorArguments(){}
}
