<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2020 iTX Technologies
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/*
 * This script checks the requirements of targeting script before interpreting.
 */

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Util\Util;

require_once "../autoload.php";

Initializer::initTerminal(true);

$file = $argv[1] ?? null;
if($file == null){
	Logger::error("Missing the script to run.");
	Logger::info("Usage: php wrapper.php <script>");
}elseif(!file_exists($file)){
	Logger::error("Script \"$file\" does not exist.");
}else{
	try{
		if(Util::verifyScriptRequirements(file_get_contents($file), $file)){
			require_once $file;
		}
	}catch(Exception $e){
		Logger::$logLevel = Logger::INFO; // Sometimes we don't need to know everything :)
		Logger::logException($e);
	}
}
