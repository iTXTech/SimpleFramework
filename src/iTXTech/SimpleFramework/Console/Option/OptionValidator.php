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

namespace iTXTech\SimpleFramework\Console\Option;

abstract class OptionValidator{
	/**
	 * Validates whether $opt is a permissible Option
	 *
	 * @param string $opt
	 *
	 * @throws \Exception
	 */
	public static function validateOption(string $opt){
		// if opt is NULL do not check further
		if($opt === null){
			return;
		}

		// handle the single character opt
		if(strlen($opt) == 1){
			$ch = $opt{0};

			if(!self::isValidOpt($ch)){
				throw new \Exception("Illegal option name '" . $ch . "'");
			}
		}else{
			for($i = 0; $i < strlen($opt); $i++){
				if(!self::isValidChar($opt{$i})){
					throw new \Exception("The option '" . $opt . "' contains an illegal "
						. "character : '" . $opt{$i} . "'");
				}
			}
		}
	}

	private static function isValidOpt(string $c){
		return self::isValidChar($c) || $c == '?' || $c == '@';
	}

	private static function isValidChar(string $c){
		return !in_array($c, ["&", "(", ")", "[", "]", "{", "}", "^", "=", ";", "!", "'", "+", ",", "`", "~"]);
	}
}
