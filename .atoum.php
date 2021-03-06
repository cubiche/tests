<?php

/**
 * This file is part of the Cubiche/Tests component.
 *
 * Copyright (c) Cubiche
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use mageekguy\atoum\visibility\extension as Extension;

/** @var \mageekguy\atoum\configurator $script */
$script->excludeDirectoriesFromCoverage(array(__DIR__.'/vendor'));

/* @var \mageekguy\atoum\runner $runner */
$runner->addTestsFromDirectory(__DIR__.'/src');
$runner->addExtension(new Extension($script));
