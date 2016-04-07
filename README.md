# Cubiche\Tests

This library provides tools to create and run tests for Cubiche components.

In each component, a `Tests/` directory contains test suites. So far, only unit
tests are supported. They are written with [atoum](http://atoum.org/).

## Installation

With [Composer](http://getcomposer.org/), to include this library into your
dependencies, you need to require [`cubiche/tests`](https://packagist.org/packages/cubiche/tests):

```json
{
    "require": {
        "cubiche/tests": "dev-master"
    }
}
```

## Automatically generate unit tests

Let's have the following class `Cubiche\Component\Foo`:

```php
namespace Cubiche\Component;

class Foo
{
    /**
     * {@inheritdoc}
     */
    public function someMethod()
    {
        // …
    }
}
```

Then, to automatically generate a test suite, we will use the `bin/test-generator
generate:class:test file` command to generate tests for some class.

Thus, to automatically generate tests of the `Cubiche\Component\Foo` class, we will make:

```sh
$ bin/test-generator generate:test:class src/Cubiche/Component/Foo.php
```

and the result is a new Test class `FooTests` and a `TestCase` class if not exists:

```
Cubiche
 |    
 +-- Component
    |-- Foo.php
    |
    +-- Tests
       |
       +-- Units
         |-- TestCase.php
         \-- FooTests.php
```
 
Let's suppose that there is another class `Cubiche\Component\Bar\Baz`:

```php
namespace Cubiche\Component\Bar;

class Baz
{
    /**
     * {@inheritdoc}
     */
    public function anotherMethod()
    {
        // …
    }
}
```

Then, to automatically generate a test suite for each classes in a directory, we will use the `bin/test-generator
generate:class:directory directory` command to generate it.

Thus, to automatically generate tests of the `Cubiche\Component` directory, we will make:

```sh
$ bin/test-generator generate:test:directory src/Cubiche/Component
```
and the result is a new set of Test classes:

```
Cubiche
 |    
 +-- Component
    |
    +-- Bar
    |  |-- Baz.php
    |   
    |-- Foo.php
    |
    +-- Tests
       |
       +-- Units
         |
         +-- Bar
         |  |--BazTests.php
         |
         |-- TestCase.php
         \-- FooTests.php
```

##Authors

[Ivannis Suárez Jérez](https://github.com/ivannis)
[Karel Osorio Ramírez](https://github.com/osorioramirez)