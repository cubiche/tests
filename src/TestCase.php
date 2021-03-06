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

use atoum\test as Test;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use mageekguy\atoum\adapter as Adapter;
use mageekguy\atoum\annotations\extractor as Extractor;
use mageekguy\atoum\asserter\generator as Generator;
use mageekguy\atoum\test\assertion\manager as Manager;
use mageekguy\atoum\tools\variable\analyzer as Analyzer;

/**
 * Abstract Test Case Class.
 *
 * @author Ivannis Suárez Jerez <ivannis.suarez@gmail.com>
 * @author Karel Osorio Ramírez <osorioramirez@gmail.com>
 */
abstract class TestCase extends Test
{
    /**
     * @var FakerGenerator
     */
    public $faker;

    /**
     * @param Adapter   $adapter
     * @param Extractor $annotationExtractor
     * @param Generator $asserterGenerator
     * @param Manager   $assertionManager
     * @param Closure   $reflectionClassFactory
     * @param Closure   $phpExtensionFactory
     * @param Analyzer  $analyzer
     */
    public function __construct(
        Adapter $adapter = null,
        Extractor $annotationExtractor = null,
        Generator $asserterGenerator = null,
        Manager $assertionManager = null,
        \Closure $reflectionClassFactory = null,
        \Closure $phpExtensionFactory = null,
        Analyzer $analyzer = null
    ) {
        parent::__construct(
            $adapter,
            $annotationExtractor,
            $asserterGenerator,
            $assertionManager,
            $reflectionClassFactory,
            $phpExtensionFactory,
            $analyzer
        );

        $this->getAsserterGenerator()->addNamespace('Cubiche\Tests\Asserters');
        $this->getAssertionManager()->setAlias('mock', 'MockAsserter');
        $this->faker = FakerFactory::create();
    }

    /**
     * {@inheritdoc}
     *
     * @see \mageekguy\atoum\test::getTestedClassName()
     */
    public function getTestedClassName()
    {
        $className = parent::getTestedClassName();

        return substr($className, 0, strrpos($className, 'Tests'));
    }

    /**
     * @param string $message
     */
    public function markTestSkipped($message = null)
    {
        return $this->skip($message);
    }

    /**
     * @return object
     */
    public function newDefaultTestedInstance()
    {
        return \call_user_func_array(array($this, 'newTestedInstance'), $this->defaultConstructorArguments());
    }

    /**
     * @return object
     */
    public function newDefaultMockTestedInstance()
    {
        return $this->newMockInstance($this->testedClass, null, null, $this->defaultConstructorArguments());
    }

    /**
     * @return array
     */
    protected function defaultConstructorArguments()
    {
        return array();
    }
}
