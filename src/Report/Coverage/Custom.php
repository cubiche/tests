<?php

/**
 * This file is part of the Cubiche/Test component.
 *
 * Copyright (c) Cubiche
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cubiche\Tests\Report\Coverage;

use mageekguy\atoum as Atoum;
use mageekguy\atoum\cli\colorizer as Colorizer;
use mageekguy\atoum\exceptions\logic\invalidArgument as InvalidArgumentException;
use mageekguy\atoum\exceptions\runtime\unexpectedValue as UnexpectedValueException;
use mageekguy\atoum\fs\path as Path;
use mageekguy\atoum\report\fields\runner\coverage\cli as Report;
use mageekguy\atoum\score\coverage as Coverage;
use mageekguy\atoum\template\parser as Parser;

/**
 * Custom class.
 *
 * @author Ivannis Suárez Jerez <ivannis.suarez@gmail.com>
 */
class Custom extends Report
{
    /**
     * Const.
     */
    const HTML_EXTENSION_FILE = '.html';

    /**
     * @var Colorizer
     */
    protected $urlColorizer = null;

    /**
     * @var string
     */
    protected $rootUrl = '';

    /**
     * @var string
     */
    protected $projectName = '';

    /**
     * @var Path
     */
    protected $sourceDirectory = null;

    /**
     * @var string
     */
    protected $templatesDirectory = null;

    /**
     * @var string
     */
    protected $destinationDirectory = null;

    /**
     * @var Parser
     */
    protected $templateParser = null;

    /**
     * @var \Closure
     */
    protected $reflectionClassInjector = null;

    /**
     * Custom constructor.
     *
     * @param $projectName
     * @param $destinationDirectory
     * @param $sourceDirectory
     */
    public function __construct($projectName, $destinationDirectory, $sourceDirectory)
    {
        parent::__construct();

        $this->sourceDirectory = new Path($sourceDirectory);

        $this
            ->setProjectName($projectName)
            ->setDestinationDirectory($destinationDirectory)
            ->setUrlColorizer()
            ->setTemplatesDirectory(__DIR__.'/../../../resources/coverage')
            ->setTemplateParser()
            ->setRootUrl('/');
    }

    /**
     * {@inheritdoc}
     *
     * @see \mageekguy\atoum\report\fields\runner\coverage\cli::__toString()
     */
    public function __toString()
    {
        $string = '';
        if (sizeof($this->coverage) > 0) {
            try {
                // clean directory
                $this->cleanDestinationDirectory();

                // generate assets
                $this->generateAssets();

                // get coverage report
                $report = $this->getCoverageReport($this->coverage);

                // generate index page for each directory
                foreach ($report['directories'] as $directoryPath => $directoryData) {
                    // build page
                    $this->buildDirectoryPage(
                        $directoryPath,
                        $directoryData,
                        $report['directories']['/']['relevantLines'],
                        $report['directories']['/']['coveredLines'],
                        $report['directories']['/']['totalLines'],
                        $report['directories']['/']['coverage']
                    );
                }

                // generate class page for each file
                foreach ($report['sources'] as $classData) {
                    // build page
                    $this->buildClassPage(
                        $classData,
                        $report['directories']['/']['relevantLines'],
                        $report['directories']['/']['coveredLines'],
                        $report['directories']['/']['totalLines'],
                        $report['directories']['/']['coverage']
                    );
                }
            } catch (\exception $exception) {
                $string .= $this->urlColorizer->colorize(
                    $this->locale->_(
                        'Unable to generate code coverage at %s: %s.',
                        $this->rootUrl,
                        $exception->getMessage()
                    )
                ).PHP_EOL;
            }
        }

        return $string;
    }

    /**
     * @param Colorizer $colorizer
     *
     * @return $this
     */
    public function setUrlColorizer(Colorizer $colorizer = null)
    {
        $this->urlColorizer = $colorizer ?: new Colorizer();

        return $this;
    }

    /**
     * @return \mageekguy\atoum\cli\colorizer
     */
    public function getUrlColorizer()
    {
        return $this->urlColorizer;
    }

    /**
     * @param string $projectName
     *
     * @return $this
     */
    public function setProjectName($projectName)
    {
        $this->projectName = (string) $projectName;

        return $this;
    }

    /**
     * @return string
     */
    public function getProjectName()
    {
        return $this->projectName;
    }

    /**
     * @return \mageekguy\atoum\fs\path
     */
    public function getSourceDirectory()
    {
        return $this->sourceDirectory;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setDestinationDirectory($path)
    {
        $this->destinationDirectory = (string) $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationDirectory()
    {
        return $this->destinationDirectory;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setTemplatesDirectory($path = null)
    {
        $this->templatesDirectory = Atoum\directory.DIRECTORY_SEPARATOR.'resources'.
            DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'coverage'
        ;

        if ($path !== null) {
            $this->templatesDirectory = $path;
        };

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplatesDirectory()
    {
        return $this->templatesDirectory;
    }

    /**
     * @param Parser $parser
     *
     * @return $this
     */
    public function setTemplateParser(Parser $parser = null)
    {
        $this->templateParser = $parser ?: new Parser();

        return $this;
    }

    /**
     * @return Parser
     */
    public function getTemplateParser()
    {
        return $this->templateParser;
    }

    /**
     * @param string $rootUrl
     *
     * @return $this
     */
    public function setRootUrl($rootUrl)
    {
        $this->rootUrl = (string) $rootUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getRootUrl()
    {
        return $this->rootUrl;
    }

    /**
     * @param Coverage $coverage
     *
     * @return array
     */
    protected function getCoverageReport(Coverage $coverage)
    {
        $sources = [];
        $directoriesCoverage = [];

        foreach ($coverage->getClasses() as $class => $file) {
            $path = new Path($file);

            $source = $this->getSourceCode($path);
            $fileName = $this->getFileName($path);
            $hierarchy = $this->getHierarchy($fileName);

            $linesCoverage = $this->getLinesCoverage($coverage->getCoverageForClass($class));
            foreach ($hierarchy as $item) {
                $itemPath = $item['path'];
                $itemName = $item['name'];
                $itemType = $item['type'];

                if (isset($directoriesCoverage[$itemPath])) {
                    $directoriesCoverage[$itemPath]['relevantLines'] += $linesCoverage['relevantLines'];
                    $directoriesCoverage[$itemPath]['coveredLines'] += $linesCoverage['coveredLines'];
                    $directoriesCoverage[$itemPath]['totalLines'] += count($linesCoverage['lines']);
                } else {
                    $directoriesCoverage[$itemPath] = [
                        'directories' => [],
                        'files' => [],
                        'relevantLines' => $linesCoverage['relevantLines'],
                        'coveredLines' => $linesCoverage['coveredLines'],
                        'totalLines' => count($linesCoverage['lines']),
                    ];
                }

                if ($itemType == 'directory') {
                    $directoriesCoverage[$itemPath]['directories'][$itemName] = [
                        'relevantLines' => 0,
                        'coveredLines' => 0,
                        'totalLines' => 0,
                    ];
                } else {
                        $calculatedCoverage = 1;
                        if ($linesCoverage['relevantLines'] > 0) {
                            $calculatedCoverage = (float) (
                                $linesCoverage['coveredLines'] / $linesCoverage['relevantLines']
                            );
                        }

                        $directoriesCoverage[$itemPath]['files'][$itemName] = [
                        'relevantLines' => $linesCoverage['relevantLines'],
                        'coveredLines' => $linesCoverage['coveredLines'],
                        'totalLines' => count($linesCoverage['lines']),
                        'coverage' => $calculatedCoverage,
                    ];
                }
            }

            foreach ($directoriesCoverage as $path => &$directoryCoverageItem) {
                $calculatedCoverage = 1;
                if ($directoryCoverageItem['relevantLines'] > 0) {
                    $calculatedCoverage = (float) (
                        $directoryCoverageItem['coveredLines'] / $directoryCoverageItem['relevantLines']
                    );
                }

                $directoryCoverageItem['coverage'] = $calculatedCoverage;
                foreach ($directoryCoverageItem['directories'] as $directoryName => &$directoryCoverage) {
                    $directoryCoverage['relevantLines'] = $directoriesCoverage[$path.$directoryName.'/']
                        ['relevantLines']
                    ;

                    $directoryCoverage['coveredLines'] = $directoriesCoverage[$path.$directoryName.'/']
                        ['coveredLines']
                    ;

                    $directoryCoverage['totalLines'] = $directoriesCoverage[$path.$directoryName.'/']
                        ['totalLines']
                    ;

                    $calculatedCoverage = 1;
                    if ($directoryCoverage['relevantLines'] > 0) {
                        $calculatedCoverage = (float) (
                            $directoryCoverage['coveredLines'] / $directoryCoverage['relevantLines']
                        );
                    }

                    $directoryCoverage['coverage'] = $calculatedCoverage;
                }
            }

            $sources[] = [
                'name' => $fileName,
                'className' => str_replace(array('.php', '/'), array('', '\\'), $fileName),
                'source' => $source,
                'coverage' => $linesCoverage,
            ];
        }

        return [
            'sources' => $sources,
            'directories' => $directoriesCoverage,
            'relevantLines' => $directoriesCoverage['/']['relevantLines'],
            'coveredLines' => $directoriesCoverage['/']['coveredLines'],
        ];
    }

    /**
     * @param array $coverage
     *
     * @return array
     */
    protected function getLinesCoverage(array $coverage)
    {
        $lines = [];
        $relevantLines = 0;
        $coveredLines = 0;

        foreach ($coverage as $method) {
            foreach ($method as $number => $line) {
                if ($number > 1) {
                    while (sizeof($lines) < ($number - 1)) {
                        $lines[] = null;
                    }
                }

                if ($line === 1) {
                    ++$relevantLines;
                    ++$coveredLines;
                    $lines[] = 1;
                } elseif ($line >= -1) {
                    ++$relevantLines;
                    $lines[] = 0;
                }
            }
        }

        $calculatedCoverage = 1;
        if ($relevantLines > 0) {
            $calculatedCoverage = (float) ($coveredLines / $relevantLines);
        }

        return [
            'totalLines' => count($lines),
            'relevantLines' => $relevantLines,
            'coveredLines' => $coveredLines,
            'coverage' => $calculatedCoverage,
            'lines' => $lines,
        ];
    }

    /**
     * @param Path $path
     *
     * @return string
     */
    protected function getFileName(Path $path)
    {
        return ltrim((string) $path->relativizeFrom($this->sourceDirectory), './');
    }

    /**
     * @param Path $path
     *
     * @return mixed
     */
    protected function getSourceCode(Path $path)
    {
        return $this->adapter->file_get_contents((string) $path->resolve());
    }

    /**
     * @param string $fileName
     *
     * @return array
     */
    protected function getHierarchy($fileName)
    {
        $directories = explode('/', $fileName);

        $path = '/';
        $result = [];
        foreach ($directories as $directory) {
            $type = strpos($directory, '.') === false ? 'directory' : 'file';

            $result[] = array(
                'name' => $directory,
                'type' => $type,
                'path' => $path,
            );

            $path = $path.$directory.'/';
        }

        return $result;
    }

    /**
     * @param $directories
     * @param $path
     * @param $level
     *
     * @return array
     */
    protected function getLevel($directories, $path, $level)
    {
        $tree = array(
            'name' => $directories[$level],
            'path' => $path,
            'children' => [],
        );

        if (($level + 1) < count($directories) - 1) {
            $path = $path.$directories[$level].'/';
            ++$level;

            $tree['children'][] = $this->getLevel($directories, $path, $level);
        }

        return $tree;
    }

    /**
     * @return \recursiveIteratorIterator
     */
    protected function getDestinationDirectoryIterator()
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->destinationDirectory,
                \FilesystemIterator::KEY_AS_PATHNAME |
                \FilesystemIterator::CURRENT_AS_FILEINFO |
                \FilesystemIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
    }

    /**
     * @return $this
     */
    protected function cleanDestinationDirectory()
    {
        try {
            foreach ($this->getDestinationDirectoryIterator() as $inode) {
                if ($inode->isDir() === false) {
                    $this->adapter->unlink($inode->getPathname());
                } elseif (($pathname = $inode->getPathname()) !== $this->destinationDirectory) {
                    $this->adapter->rmdir($pathname);
                }
            }
        } catch (\Exception $exception) {
            return $this;
        }

        return $this;
    }

    /**
     * Generate the assets.
     */
    protected function generateAssets()
    {
        // copy assets
        $this->adapter->copy(
            $this->templatesDirectory.DIRECTORY_SEPARATOR.'screen.css',
            $this->destinationDirectory.DIRECTORY_SEPARATOR.'screen.css'
        );

        $this->adapter->copy(
            $this->templatesDirectory.DIRECTORY_SEPARATOR.'application.js',
            $this->destinationDirectory.DIRECTORY_SEPARATOR.'application.js'
        );

        $this->adapter->copy(
            $this->templatesDirectory.DIRECTORY_SEPARATOR.'logo.png',
            $this->destinationDirectory.DIRECTORY_SEPARATOR.'logo.png'
        );
    }

    /**
     * @param $template
     * @param $projectCoverage
     * @param $itemCoverage
     * @param $itemPath
     */
    protected function buildCommonTemplate($template, $projectCoverage, $itemCoverage, $itemPath)
    {
        // global variables
        $template->projectName = $this->projectName;
        $template->rootUrl = $this->rootUrl;
        $template->relativeRootUrl = rtrim(str_repeat('../', substr_count($itemPath, '\\')), DIRECTORY_SEPARATOR).
            DIRECTORY_SEPARATOR
        ;

        $template->relevantLines = $projectCoverage['relevantLines'];
        $template->coveredLines = $projectCoverage['coveredLines'];

        $template->itemTotalLines = $itemCoverage['totalLines'];
        $template->itemRelevantLines = $itemCoverage['relevantLines'];
        $template->itemCoveredLines = $itemCoverage['coveredLines'];

        // breadcrumb
        $pathTemplate = $template->pathTemplate;
        $pathItemTemplate = $pathTemplate->pathItem;
        $pathItemLastTemplate = $pathTemplate->pathItemLast;

        $breadcrumb = array_filter(explode('/', $itemPath));
        if (count($breadcrumb) > 0) {
            $pathItemTemplate->build(array(
                'pathItemName' => 'Home',
                'pathItemUrl' => DIRECTORY_SEPARATOR,
            ));
        }

        $pathItemUrl = '';
        $i = 0;
        foreach ($breadcrumb as $bread) {
            if ($i++ == count($breadcrumb) - 1) {
                $pathItemLastTemplate->build(array(
                    'pathItemName' => $bread,
                ));
            } else {
                $pathItemUrl .= DIRECTORY_SEPARATOR.$bread;
                $pathItemTemplate->build(array(
                    'pathItemName' => $bread,
                    'pathItemUrl' => $pathItemUrl,
                ));
            }
        }

        $pathTemplate->build();

        // global coverage
        if ($projectCoverage['coverage'] === null) {
            $template->coverageUnavailable->build();
        } else {
            $template->coverageAvailable->build(
                array(
                    'coverageValue' => round($projectCoverage['coverage'] * 100, 2),
                    'uncoverageValue' => 100 - round($projectCoverage['coverage'] * 100, 2),
                )
            );
        }

        // item coverage
        if ($itemCoverage['coverage'] === null) {
            $template->itemCoverageUnavailable->build();
        } else {
            $template->itemCoverageAvailable->build(
                array(
                    'itemCoverageValue' => round($itemCoverage['coverage'] * 100, 2),
                    'itemUncoverageValue' => 100 - round($itemCoverage['coverage'] * 100, 2),
                    'itemCoverageRounded' => floor($itemCoverage['coverage'] * 100),
                )
            );
        }
    }

    /**
     * @param string $directoryPath
     * @param array  $directoryData
     * @param int    $relevantLines
     * @param int    $coveredLines
     * @param int    $totalLines
     * @param int    $coverage
     */
    protected function buildDirectoryPage(
        $directoryPath,
        array $directoryData,
        $relevantLines,
        $coveredLines,
        $totalLines,
        $coverage
    ) {
        // get template
        $template = $this->templateParser->parseFile(
            $this->templatesDirectory.DIRECTORY_SEPARATOR.
            ($directoryPath == '/' ? 'index.tpl' : 'directory.tpl')
        );

        // build common
        $this->buildCommonTemplate(
            $template,
            array(
                'totalLines' => $totalLines,
                'relevantLines' => $relevantLines,
                'coveredLines' => $coveredLines,
                'coverage' => $coverage,
            ),
            $directoryData,
            $directoryPath
        );

        // directories coverage
        $itemsTemplate = $template->items;
        $itemTemplate = $itemsTemplate->item;

        foreach ($directoryData['directories'] as $directoryName => $directoryValues) {
            $itemTemplate->build(array(
                'itemName' => $directoryName,
                'itemUrl' => $directoryPath.$directoryName,
                'itemType' => 'directory',
                'itemTotalLines' => $directoryValues['totalLines'],
                'itemRelevantLines' => $directoryValues['relevantLines'],
                'itemCoveredLines' => $directoryValues['coveredLines'],
                'itemCoverage' => round($directoryValues['coverage'] * 100, 2),
                'itemCoverageRounded' => floor($directoryValues['coverage'] * 100),
            ));
        }

        foreach ($directoryData['files'] as $fileName => $fileValues) {
            $itemTemplate->build(array(
                'itemName' => $fileName,
                'itemUrl' => str_replace('.php', self::HTML_EXTENSION_FILE, $directoryPath.$fileName),
                'itemType' => 'file',
                'itemTotalLines' => $fileValues['totalLines'],
                'itemRelevantLines' => $fileValues['relevantLines'],
                'itemCoveredLines' => $fileValues['coveredLines'],
                'itemCoverage' => round($fileValues['coverage'] * 100, 2),
                'itemCoverageRounded' => floor($fileValues['coverage'] * 100),
            ));
        }

        $itemsTemplate->build();

        $file = $this->destinationDirectory.$directoryPath.'index.html';
        $directory = $this->adapter->dirname($file);
        if ($this->adapter->is_dir($directory) === false) {
            $this->adapter->mkdir($directory, 0777, true);
        }

        $this->adapter->file_put_contents($file, (string) $template->build());
    }

    /**
     * @param array $classData
     * @param int   $relevantLines
     * @param int   $coveredLines
     * @param int   $totalLines
     * @param int   $coverage
     */
    protected function buildClassPage(
        $classData,
        $relevantLines,
        $coveredLines,
        $totalLines,
        $coverage
    ) {
        // get template
        $template = $this->templateParser->parseFile(
            $this->templatesDirectory.DIRECTORY_SEPARATOR.'class.tpl'
        );

        // build common
        $this->buildCommonTemplate(
            $template,
            array(
                'totalLines' => $totalLines,
                'relevantLines' => $relevantLines,
                'coveredLines' => $coveredLines,
                'coverage' => $coverage,
            ),
            $classData['coverage'],
            $classData['name']
        );

        $methodsTemplates = $template->methods;
        $methodTemplates = $methodsTemplates->method;

        $methodCoverageAvailableTemplates = $methodTemplates->methodCoverageAvailable;
        $methodCoverageUnavailableTemplates = $methodTemplates->methodCoverageUnavailable;

        $sourceFileTemplates = $template->sourceFile;
        $templates = array(
            'lineTemplates' => $sourceFileTemplates->line,
            'coveredLineTemplates' => $sourceFileTemplates->coveredLine,
            'notCoveredLineTemplates' => $sourceFileTemplates->notCoveredLine,
        );

        $className = $classData['className'];
        $template->className = $className;
        $methods = $this->coverage->getCoverageForClass($className);

        $reflectedMethods = array();
        $reflectionClassMethods = $this->getReflectionClass($className)->getMethods();
        foreach (array_filter($reflectionClassMethods, function ($reflectedMethod) use ($className) {
            return $reflectedMethod->isAbstract() === false &&
            $reflectedMethod->getDeclaringClass()->getName() === $className;
        }) as $reflectedMethod) {
            $reflectedMethods[$reflectedMethod->getName()] = $reflectedMethod;
        }

        if (sizeof($reflectedMethods) > 0) {
            foreach (array_intersect(array_keys($reflectedMethods), array_keys($methods)) as $methodName) {
                $methodCoverageValue = $this->coverage->getValueForMethod($className, $methodName);

                if ($methodCoverageValue === null) {
                    $methodCoverageUnavailableTemplates->build(array('methodName' => $methodName));
                } else {
                    $methodCoverageAvailableTemplates->build(array(
                        'methodName' => $methodName,
                        'methodCoverageValue' => round($methodCoverageValue * 100, 2),
                        'methodCoverageRounded' => ceil($methodCoverageValue * 100),
                    ));
                }

                $methodTemplates->build();

                $methodCoverageAvailableTemplates->resetData();
                $methodCoverageUnavailableTemplates->resetData();
            }

            $methodsTemplates->build();
            $methodTemplates->resetData();
        }

        $srcFile = $this->adapter->fopen(
            $this->sourceDirectory->getRealPath()->__toString().DIRECTORY_SEPARATOR.$classData['name'],
            'r'
        );

        if ($srcFile !== false) {
            $methodLines = array();

            foreach ($reflectedMethods as $reflectedMethodName => $reflectedMethod) {
                $methodLines[$reflectedMethod->getStartLine()] = $reflectedMethodName;
            }

            for ($currentMethod = null, $lineNumber = 1, $line = $this->adapter->fgets($srcFile);
                 $line !== false;
                 $lineNumber++, $line = $this->adapter->fgets($srcFile)) {
                if (isset($methodLines[$lineNumber]) === true) {
                    $currentMethod = $methodLines[$lineNumber];
                }

                switch (true) {
                    case isset($methods[$currentMethod]) === false || (
                            isset($methods[$currentMethod][$lineNumber]) === false ||
                            $methods[$currentMethod][$lineNumber] == -2):
                        $lineTemplateName = 'lineTemplates';
                        break;

                    case isset($methods[$currentMethod]) === true &&
                        isset($methods[$currentMethod][$lineNumber]) === true &&
                        $methods[$currentMethod][$lineNumber] == -1:
                        $lineTemplateName = 'notCoveredLineTemplates';
                        break;

                    default:
                        $lineTemplateName = 'coveredLineTemplates';
                }
                $templates[$lineTemplateName]->lineNumber = $lineNumber;
                $templates[$lineTemplateName]->code = htmlentities($line, ENT_QUOTES, 'UTF-8');

                if (isset($methodLines[$lineNumber]) === true) {
                    foreach ($templates[$lineTemplateName]->anchor as $anchorTemplate) {
                        $anchorTemplate->resetData();
                        $anchorTemplate->method = $currentMethod;
                        $anchorTemplate->build();
                    }
                }

                $templates[$lineTemplateName]
                    ->addToParent()
                    ->resetData();
            }

            $this->adapter->fclose($srcFile);
        }

        $file = $this->destinationDirectory.DIRECTORY_SEPARATOR.
            str_replace('\\', '/', $className).self::HTML_EXTENSION_FILE
        ;

        $directory = $this->adapter->dirname($file);
        if ($this->adapter->is_dir($directory) === false) {
            $this->adapter->mkdir($directory, 0777, true);
        }

        $this->adapter->file_put_contents($file, (string) $template->build());
    }

    /**
     * @param \Closure $reflectionClassInjector
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setReflectionClassInjector(\Closure $reflectionClassInjector)
    {
        $closure = new \ReflectionMethod($reflectionClassInjector, '__invoke');

        if ($closure->getNumberOfParameters() != 1) {
            throw new InvalidArgumentException('Reflection class injector must take one argument');
        }

        $this->reflectionClassInjector = $reflectionClassInjector;

        return $this;
    }

    /**
     * @param $class
     *
     * @return \ReflectionClass
     *
     * @throws UnexpectedValueException
     */
    public function getReflectionClass($class)
    {
        if ($this->reflectionClassInjector === null) {
            $reflectionClass = new \ReflectionClass($class);
        } else {
            $reflectionClass = $this->reflectionClassInjector->__invoke($class);

            if ($reflectionClass instanceof \ReflectionClass === false) {
                throw new UnexpectedValueException(
                    'Reflection class injector must return a \reflectionClass instance'
                );
            }
        }

        return $reflectionClass;
    }
}
