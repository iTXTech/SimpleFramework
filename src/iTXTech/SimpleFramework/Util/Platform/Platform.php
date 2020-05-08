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

	public static function newStr(string $str) : \FFI\CData{
		$d = FFI::new("char[" . (strlen($str) + 1) . "]", false);
		for($i = 0; $i < strlen($str); $i++){
			$d[$i] = $str[$i];
		}
		$d[$i] = "\0";
		return $d;
	}

	public static function isSupported() : bool{
		return in_array(Util::getOS(), [Util::OS_WINDOWS]);
	}
}
