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
 * @author iTXTech
 * @link https://itxtech.org
 *
 */

namespace iTXTech\SimpleFramework\Console;

/**
 * Class used to handle Minecraft chat format, and convert it to other formats like ANSI or HTML
 */
abstract class TextFormat{
	const ESCAPE = "\xc2\xa7"; //§
	
	const BLACK = TextFormat::ESCAPE . "0";
	const DARK_BLUE = TextFormat::ESCAPE . "1";
	const DARK_GREEN = TextFormat::ESCAPE . "2";
	const DARK_AQUA = TextFormat::ESCAPE . "3";
	const DARK_RED = TextFormat::ESCAPE . "4";
	const DARK_PURPLE = TextFormat::ESCAPE . "5";
	const GOLD = TextFormat::ESCAPE . "6";
	const GRAY = TextFormat::ESCAPE . "7";
	const DARK_GRAY = TextFormat::ESCAPE . "8";
	const BLUE = TextFormat::ESCAPE . "9";
	const GREEN = TextFormat::ESCAPE . "a";
	const AQUA = TextFormat::ESCAPE . "b";
	const RED = TextFormat::ESCAPE . "c";
	const LIGHT_PURPLE = TextFormat::ESCAPE . "d";
	const YELLOW = TextFormat::ESCAPE . "e";
	const WHITE = TextFormat::ESCAPE . "f";

	const OBFUSCATED = TextFormat::ESCAPE . "k";
	const BOLD = TextFormat::ESCAPE . "l";
	const STRIKETHROUGH = TextFormat::ESCAPE . "m";
	const UNDERLINE = TextFormat::ESCAPE . "n";
	const ITALIC = TextFormat::ESCAPE . "o";
	const RESET = TextFormat::ESCAPE . "r";

	/**
	 * Splits the string by Format tokens
	 *
	 * @param string $string
	 *
	 * @return array
	 */
	public static function tokenize($string){
		return preg_split("/(". TextFormat::ESCAPE ."[0123456789abcdefklmnor])/", $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	}

	/**
	 * Cleans the string from Minecraft codes and ANSI Escape Codes
	 *
	 * @param string $string
	 * @param bool   $removeFormat
	 *
	 * @return mixed
	 */
	public static function clean($string, $removeFormat = true){
		if($removeFormat){
			return str_replace(TextFormat::ESCAPE, "", preg_replace(["/". TextFormat::ESCAPE ."[0123456789abcdefklmnor]/", "/\x1b[\\(\\][[0-9;\\[\\(]+[Bm]/"], "", $string));
		}
		return str_replace("\x1b", "", preg_replace("/\x1b[\\(\\][[0-9;\\[\\(]+[Bm]/", "", $string));
	}

	/**
	 * Returns a string with colorized ANSI Escape codes
	 *
	 * @param $string
	 *
	 * @return string
	 */
	public static function toANSI($string){
		if(!is_array($string)){
			$string = self::tokenize($string);
		}

		$newString = "";
		foreach($string as $token){
			switch($token){
				case TextFormat::BOLD:
					$newString .= Terminal::$FORMAT_BOLD;
					break;
				case TextFormat::OBFUSCATED:
					$newString .= Terminal::$FORMAT_OBFUSCATED;
					break;
				case TextFormat::ITALIC:
					$newString .= Terminal::$FORMAT_ITALIC;
					break;
				case TextFormat::UNDERLINE:
					$newString .= Terminal::$FORMAT_UNDERLINE;
					break;
				case TextFormat::STRIKETHROUGH:
					$newString .= Terminal::$FORMAT_STRIKETHROUGH;
					break;
				case TextFormat::RESET:
					$newString .= Terminal::$FORMAT_RESET;
					break;

				//Colors
				case TextFormat::BLACK:
					$newString .= Terminal::$COLOR_BLACK;
					break;
				case TextFormat::DARK_BLUE:
					$newString .= Terminal::$COLOR_DARK_BLUE;
					break;
				case TextFormat::DARK_GREEN:
					$newString .= Terminal::$COLOR_DARK_GREEN;
					break;
				case TextFormat::DARK_AQUA:
					$newString .= Terminal::$COLOR_DARK_AQUA;
					break;
				case TextFormat::DARK_RED:
					$newString .= Terminal::$COLOR_DARK_RED;
					break;
				case TextFormat::DARK_PURPLE:
					$newString .= Terminal::$COLOR_PURPLE;
					break;
				case TextFormat::GOLD:
					$newString .= Terminal::$COLOR_GOLD;
					break;
				case TextFormat::GRAY:
					$newString .= Terminal::$COLOR_GRAY;
					break;
				case TextFormat::DARK_GRAY:
					$newString .= Terminal::$COLOR_DARK_GRAY;
					break;
				case TextFormat::BLUE:
					$newString .= Terminal::$COLOR_BLUE;
					break;
				case TextFormat::GREEN:
					$newString .= Terminal::$COLOR_GREEN;
					break;
				case TextFormat::AQUA:
					$newString .= Terminal::$COLOR_AQUA;
					break;
				case TextFormat::RED:
					$newString .= Terminal::$COLOR_RED;
					break;
				case TextFormat::LIGHT_PURPLE:
					$newString .= Terminal::$COLOR_LIGHT_PURPLE;
					break;
				case TextFormat::YELLOW:
					$newString .= Terminal::$COLOR_YELLOW;
					break;
				case TextFormat::WHITE:
					$newString .= Terminal::$COLOR_WHITE;
					break;
				default:
					$newString .= $token;
					break;
			}
		}

		return $newString;
	}

}
