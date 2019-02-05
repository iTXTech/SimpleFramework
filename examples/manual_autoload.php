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
define("iTXTech\SimpleFramework\DISABLE_AUTO_INIT", true);

require_once "../autoload.php";

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Initializer;

Initializer::loadSimpleFramework("sf.phar");
Initializer::initClassLoader();

Logger::info("Succ");
