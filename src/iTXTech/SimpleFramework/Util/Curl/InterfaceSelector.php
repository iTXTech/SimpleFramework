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
