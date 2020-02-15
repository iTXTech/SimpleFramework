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

namespace iTXTech\SimpleFramework {

	use iTXTech\SimpleFramework\Console\Terminal;

	if(!defined("SF_LOADER_AUTO_INIT") or SF_LOADER_AUTO_INIT){
		Initializer::loadSimpleFramework();
		Initializer::initClassLoader();
	}

	abstract class Initializer{
		/** @var \ClassLoader */
		private static $classLoader;

		public static function loadSimpleFramework(string $phar = "SimpleFramework.phar",
		                                           string $workingDir = __DIR__){
			$workingDir .= DIRECTORY_SEPARATOR;
			if(file_exists($phar)){
				define("iTXTech\SimpleFramework\PATH", "phar://" . $phar . DIRECTORY_SEPARATOR);
			}elseif(file_exists($workingDir . $phar)){
				define("iTXTech\SimpleFramework\PATH", "phar://" . $workingDir . $phar . DIRECTORY_SEPARATOR);
			}else{
				define("iTXTech\SimpleFramework\PATH", $workingDir);
			}
			require_once(PATH . "src/iTXTech/SimpleFramework/Util/ClassLoader.php");
		}

		public static function initClassLoader(){
			self::$classLoader = new \ClassLoader();
			self::$classLoader->addPath(PATH . "src");
			self::$classLoader->register(true);
		}

		public static function getClassLoader() : \ClassLoader{
			return self::$classLoader;
		}

		/**
	 	* Initiate Terminal
	 	*
	 	* @param bool|null $formattingCodes
	 	*/
		public static function initTerminal(?bool $formattingCodes = null){
			Terminal::$formattingCodes = $formattingCodes;
			Terminal::init();
		}
	}
}
