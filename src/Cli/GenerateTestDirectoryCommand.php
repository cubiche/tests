<?php

/**
 * This file is part of the Cubiche/Test component.
 *
 * Copyright (c) Cubiche
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cubiche\Tests\Cli;

use Cubiche\Tests\Generator\ClassUtils;
use Cubiche\Tests\Generator\TestCaseGenerator;
use Cubiche\Tests\Generator\TestGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * GenerateTestDirectoryCommand class.
 *
 * @author Ivannis Suárez Jerez <ivannis.suarez@gmail.com>
 */
class GenerateTestDirectoryCommand extends BaseCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('generate:test:directory')
            ->setDescription('Generate a test classes based on a directory')
            ->addArgument(
                'directory',
                InputArgument::REQUIRED,
                'The directory to generate a test classes for'
            )
        ;

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function getGenerators(InputInterface $input, OutputInterface $output)
    {
        $directoryName = $input->getArgument('directory');
        $directory = realpath($directoryName);

        if (!is_dir($directory)) {
            throw new \RuntimeException(
                sprintf(
                    'Invalid directory path "%s".',
                    $directory
                )
            );
        }

        $generators = [];
        foreach (ClassUtils::getFilesInDirectory($directory, $this->getTestsDirectoryName(), 'php') as $fileName) {
            $classes = ClassUtils::getClassesInFile($fileName);
            if (empty($classes)) {
                $interfaces = ClassUtils::getInterfacesInFile($fileName);
                if (!empty($interfaces)) {
                    continue;
                }

                $output->writeln(
                    sprintf(
                        '<comment>Could not find class in "%s".</comment>',
                        $fileName
                    )
                );
            }

            foreach ($classes as $className) {
                $generators[] = new TestGenerator(
                    $className,
                    $this->getTestsCaseClassName(),
                    $this->getTestsDirectoryName()
                );
            }
        }

        if (!empty($generators)) {
            $testCaseGenerator = new TestCaseGenerator(
                $generators[0]->getFullClassName(),
                $this->getTestsCaseClassName(),
                $this->getTestsDirectoryName()
            );

            if (!is_file($testCaseGenerator->getTargetSourceFile())) {
                $generators[] = $testCaseGenerator;
            }
        }

        return $generators;
    }
}
