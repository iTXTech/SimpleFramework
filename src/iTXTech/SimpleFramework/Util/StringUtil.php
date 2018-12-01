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

namespace iTXTech\SimpleFramework\Util;

abstract class StringUtil{
	public static function startsWith(string $str, string $prefix) : bool{
		return substr($str, 0, strlen($prefix)) === $prefix;
	}

	public static function endsWith(string $str, string $suffix) : bool{
		return substr($str, strlen($str) - strlen($suffix), strlen($suffix)) === $suffix;
	}

	public static function contains(string $str, string $s) : bool{
		return strpos($str, $s) > 0;
	}

	public static function between(string $string, string $after, string $before, int $offset = 0) : string{
		return substr($string, $pos = strpos($string, $after, $offset) + strlen($after),
			strpos($string, $before, $pos) - $pos);
	}
}
