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

namespace iTXTech\SimpleFramework\Util\Platform;

use FFI;
use FFI\CData;
use iTXTech\SimpleFramework\Util\Util;

abstract class Platform{
	public static function checkExtension(){
		if(!extension_loaded("ffi")){
			throw new \RuntimeException("FFI extension is not available, which requires PHP 7.4+.");
		}
	}

	public static function init(){
		if(extension_loaded("ffi")){
			WindowsPlatform::init();
		}
	}

	public static function newStr(string $str, int $nulls = 1, string $type = "char") : CData{
		$d = FFI::new("{$type}[" . (strlen($str) + $nulls) . "]", false);
		for($i = 0; $i < strlen($str); $i++){
			$d[$i] = $str[$i];
		}
		for($j = $i; $j < $i + $nulls; $j++){
			$d[$j] = "\0";
		}
		return $d;
	}

	public static function newArr(string $type, array $arr) : CData{
		$d = FFI::new("{$type}[" . count($arr) . "]", false);
		for($i = 0; $i < count($arr); $i++){
			$d[$i] = $arr[$i];
		}
		return $d;
	}

	public static function data($d){
		return ($d instanceof CData) ? $d->cdata : $d;
	}

	public static function isSupported() : bool{
		return in_array(Util::getOS(), [Util::OS_WINDOWS]);
	}
}
