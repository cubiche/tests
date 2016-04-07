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

use Symfony\Component\Console\Application as BaseApplication;

/**
 * Application class.
 *
 * @author Ivannis Suárez Jerez <ivannis.suarez@gmail.com>
 */
class Application extends BaseApplication
{
    /**
     * Application constructor.
     */
    public function __construct()
    {
        parent::__construct('Cubiche Test Generator');

        $this->add(new GenerateTestClassCommand());
        $this->add(new GenerateTestDirectoryCommand());
    }
}