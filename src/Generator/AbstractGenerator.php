<?php

/**
 * This file is part of the Cubiche/Test component.
 *
 * Copyright (c) Cubiche
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cubiche\Tests\Generator;

/**
 * AbstractGenerator class.
 *
 * @author Ivannis Suárez Jerez <ivannis.suarez@gmail.com>
 */
abstract class AbstractGenerator
{
    /**
     * @var array
     */
    protected $className;

    /**
     * @var string
     */
    protected $testDirectoryName;

    /**
     * @var integer
     */
    protected $level;

    /**
     * @var string
     */
    protected $sourceFile;

    /**
     * @var array
     */
    protected $targetClassName;

    /**
     * @var string
     */
    protected $targetSourceFile;

    /**
     * AbstractGenerator constructor.
     *
     * @param string  $className
     * @param string  $testDirectoryName
     * @param integer $level
     * @param string  $sourceFile
     * @param string  $targetClassName
     * @param string  $targetSourceFile
     */
    public function __construct(
        $className,
        $testDirectoryName,
        $level = 3,
        $sourceFile = '',
        $targetClassName = '',
        $targetSourceFile = ''
    ) {
        $this->testDirectoryName = $testDirectoryName;
        $this->level = $level;

        if (empty($sourceFile)) {
            $sourceFile = ClassUtils::resolveSourceFile($className);
        }

        $sourceFile = realpath($sourceFile);
        include_once $sourceFile;

        if (!class_exists($className)) {
            throw new \RuntimeException(
                sprintf(
                    'Could not find class "%s" in "%s".',
                    $className,
                    $sourceFile
                )
            );
        }

        if (empty($targetClassName)) {
            $targetClassName = $this->resolveTargetClassName($className, $testDirectoryName, $this->level);
        }

        if (empty($targetSourceFile)) {
            $targetSourceFile = ClassUtils::resolveTargetSourceFile(
                $className,
                $sourceFile,
                $targetClassName
            );
        }

        $this->className = ClassUtils::extractClassNameInfo($className);
        $this->targetClassName = ClassUtils::extractClassNameInfo($targetClassName);

        $this->sourceFile = $sourceFile;
        $this->targetSourceFile = $targetSourceFile;
    }

    /**
     * @param string  $className
     * @param string  $testDirectoryName
     * @param integer $level
     *
     * @return string
     */
    public function resolveTargetClassName($className, $testDirectoryName, $level = 3)
    {
        return ClassUtils::resolveTargetClassName(
            $className,
            $testDirectoryName,
            $level,
            $this->isTestCaseClass()
        );
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className['className'];
    }

    /**
     * @return string
     */
    public function getFullClassName()
    {
        return $this->className['fullClassName'];
    }

    /**
     * @return string
     */
    public function getSourceFile()
    {
        return $this->sourceFile;
    }

    /**
     * @return string
     */
    public function getTargetClassName()
    {
        return $this->targetClassName['fullClassName'];
    }

    /**
     * @return string
     */
    public function getTargetSourceFile()
    {
        return $this->targetSourceFile;
    }

    /**
     * @param string $file
     * @param bool   $force
     *
     * @return int
     */
    public function write($file = '', $force = false)
    {
        if ($file == '') {
            $file = $this->targetSourceFile;
        }

        if (!is_file($file) || $force) {
            $directory = dirname($file);
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            return file_put_contents($file, $this->generate());
        }

        throw new \RuntimeException('The file exists');
    }

    /**
     * @return bool
     */
    abstract protected function isTestCaseClass();

    /**
     * @return string
     */
    abstract public function generate();
}
