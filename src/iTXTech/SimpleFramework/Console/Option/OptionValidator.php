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

namespace iTXTech\SimpleFramework\Console\Option;

abstract class OptionValidator{
	/**
	 * Validates whether $opt is a permissible Option
	 *
	 * @param string $opt
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function validateOption(string $opt){
		// if opt is NULL do not check further
		if($opt === null){
			return;
		}

		// handle the single character opt
		if(strlen($opt) == 1){
			$ch = $opt[0];

			if(!self::isValidOpt($ch)){
				throw new \InvalidArgumentException("Illegal option name '" . $ch . "'");
			}
		}else{
			for($i = 0; $i < strlen($opt); $i++){
				if(!self::isValidChar($opt[$i])){
					throw new \InvalidArgumentException("The option '" . $opt .
						"' contains an illegal character : '" . $opt[$i] . "'");
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
