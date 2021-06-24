<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2021 iTX Technologies
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

namespace iTXTech\SimpleFramework\Util\Curl;

abstract class InterfaceSelector{
	private static $GLOBAL_INTERFACES = "";
	private static $INTERFACES = [];//compatible with multi threads

	public static function registerInterface(string $if, int $chance = 1){
		for($i = 0; $i < $chance; $i++){
			self::$GLOBAL_INTERFACES .= $if . ";";
		}
		self::$GLOBAL_INTERFACES = substr(self::$GLOBAL_INTERFACES, 1);
	}

	public static function select() : string{
		if(self::$GLOBAL_INTERFACES == ""){
			return "";
		}
		if(self::$INTERFACES == []){
			self::$INTERFACES = explode(";", self::$GLOBAL_INTERFACES);
		}
		return self::$INTERFACES[array_rand(self::$INTERFACES)];
	}
}
