<?php

/*
 *
 * SimpleFramework
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

//disable autoload.php
define("SF_LOADER_AUTO_INIT", false);

require_once "../autoload.php";

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Terminal;
use iTXTech\SimpleFramework\Initializer;

Initializer::loadSimpleFramework("sf.phar");
Initializer::initClassLoader();

Terminal::init();
Logger::info("SimpleFramework Location: " . iTXTech\SimpleFramework\PATH);
