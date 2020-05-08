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
 * CAUTION: This example will change your desktop background image.
 * This example shows how to interact with Microsoft Windows APIs.
 * For more APIs and definitions, see https://docs.microsoft.com
 */

/*
SF_SCRIPT_REQUIREMENTS_STARTS
{"php":7.4,"exts":{"ffi":""},"os":"win","info":"New callback syntax only supports PHP7.4+"}
SF_SCRIPT_REQUIREMENTS_ENDS
 */

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Util\Platform\WindowsPlatform;

require_once "../autoload.php";

Initializer::initTerminal(true);

function run(string $name, callable ...$funcs){
	$lastResult = null;
	$results = [];
	foreach($funcs as $func){
		$results[] = $lastResult = (($lastResult == null) ? $func() : $func($lastResult)) .
			(" / " . WindowsPlatform::getLastError());
	}
	Logger::info("$name result is " . implode(", ", $results));
}

run("MessageBox",
	fn() => WindowsPlatform::messageBox("Hello from Win32 native!", "SimpleFramework", 0x01 | 0x40)
);
run("SetBackgroundImage",
	fn() => WindowsPlatform::setSystemParametersInfo(0x14, 0, "D:\\1.png", 0x1 | 0x2),
	fn($result) => WindowsPlatform::messageBox("Result of Last Operation: $result", "SimpleFramework")
);
run("SetSystemProxy",
	fn() => WindowsPlatform::setSystemProxyOptions(false, "http://localhost:531", "local"),
	// TODO: get
	fn() => WindowsPlatform::setSystemProxyOptions(true)
);

Logger::info("Script End.");
