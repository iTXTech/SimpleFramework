<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2022 iTX Technologies
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
		Framework::init();

		//backward compatibility
		$classLoader = Initializer::getClassLoader();
	}

	abstract class Initializer{
		/** @var \ClassLoader */
		private static $classLoader;

		public static function loadSimpleFramework(string $phar = "SimpleFramework.phar",
												   string $workingDir = __DIR__){
			$workingDir .= DIRECTORY_SEPARATOR;
			define("iTXTech\SimpleFramework\PATH", $workingDir);
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

		public static function setSingleThread(bool $bool = false){
			@define('iTXTech\SimpleFramework\SINGLE_THREAD', $bool);
			if(!$bool){
				ThreadManager::init();
			}
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

		public static function loadCli(){
			global $argv;
			require_once PATH . "src/iTXTech/SimpleFramework/SimpleFramework.php";
		}
	}
}
