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
 *
 * Try:
 * php .\wrapper.php .\windows.php
 * php .\wrapper.php .\windows.php a
 */

/*
SF_SCRIPT_REQUIREMENTS_STARTS
{"php":7.4,"exts":{"ffi":""},"os":"win","info":"New callback syntax only supports PHP7.4+"}
SF_SCRIPT_REQUIREMENTS_ENDS
 */

use FFI\CData;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Util\Platform\Platform;
use iTXTech\SimpleFramework\Util\Platform\WindowsPlatform;

require_once "../autoload.php";

Initializer::initTerminal(true);

function run(string $name, callable ...$funcs){
	$lastResult = null;
	$results = [];
	foreach($funcs as $func){
		$lastResult = $func($lastResult);
		@$results[] = $lastResult . " / " . WindowsPlatform::getLastError();
	}
	Logger::info("$name result is " . implode(", ", $results));
}

run("RegistryReadKey",
	fn() => var_dump(WindowsPlatform::regGetValue(WindowsPlatform::HKEY_LOCAL_MACHINE, "SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion",
		"DigitalProductId", 0xffff)),
	fn() => "Version: " . WindowsPlatform::regGetValue(WindowsPlatform::HKEY_LOCAL_MACHINE,
			"SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion",
			"CurrentVersion", 0xffff),
	fn($ver) => $ver . ", MajorVer: " . WindowsPlatform::regGetValue(WindowsPlatform::HKEY_LOCAL_MACHINE,
			"SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion", "CurrentMajorVersionNumber", 0xffff),
	fn($ver) => PHP_EOL . $ver . ", Edition: " . WindowsPlatform::regGetValue(WindowsPlatform::HKEY_LOCAL_MACHINE,
			"SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion", "EditionID", 0xffff)
);

run("RegistryOperation",
	fn() => WindowsPlatform::regCreateKey(WindowsPlatform::HKEY_LOCAL_MACHINE, "SOFTWARE\\iTXTech\\SimpleFramework"),
	function($arr){
		if(is_array($arr)){
			$k = $arr[0];
			WindowsPlatform::regSetValue($k, "TestStr", WindowsPlatform::REG_SZ, "wdnmd");
			WindowsPlatform::regSetValue($k, "TestDWORD", WindowsPlatform::REG_DWORD, 233);
			return $arr[0];
		}
		return 0;
	},
	function($k){
		WindowsPlatform::messageBox("Check your Registry: HKLM\\SOFTWARE\\iTXTech\\SimpleFramework", "SimpleFramework");
		return $k;
	},
	function($k){
		WindowsPlatform::regDeleteValue($k, "TestStr");
		WindowsPlatform::regDeleteValue($k, "TestDWORD");
		return $k;
	},
	function($k){
		WindowsPlatform::messageBox("Now check again: HKLM\\SOFTWARE\\iTXTech\\SimpleFramework", "SimpleFramework");
		return $k;
	},
	function($key){
		if($key instanceof CData){
			WindowsPlatform::regCloseKey($key);
			WindowsPlatform::regDeleteKey(WindowsPlatform::HKEY_LOCAL_MACHINE, "SOFTWARE\\iTXTech\\SimpleFramework");
			WindowsPlatform::regDeleteKey(WindowsPlatform::HKEY_LOCAL_MACHINE, "SOFTWARE\\iTXTech");
		}
	}
);

run("MessageBox",
	fn() => WindowsPlatform::messageBox("Hello from Win32 native!", "SimpleFramework", 0x01 | 0x40)
);
run("SetBackgroundImage",
	fn() => WindowsPlatform::systemParametersInfo(0x14, 0, "D:\\1.png", 0x1 | 0x2),
	function(){
		$str = Platform::newStr("");
		WindowsPlatform::systemParametersInfo(0x73, 260, FFI::addr($str), 0); //0x73 = SPI_GETDESKWALLPAPER, 260 = MAX_PATH
		return FFI::string($str);
	},
	fn($result) => WindowsPlatform::messageBox("Result of Last Operation: $result", "SimpleFramework")
);
run("SetSystemProxy",
	fn() => WindowsPlatform::setSystemProxyOptions(false, "http://localhost:531", "local"),
	fn() => WindowsPlatform::getSystemProxyOptions(),
	function($result){
		WindowsPlatform::messageBox("Get Proxy Info: " . $result[1], "SimpleFramework");
		return WindowsPlatform::setSystemProxyOptions(true);
	}
);

Logger::info("Script End. Ctrl+C to exit.");

while(true) ;
