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

namespace iTXTech\SimpleFramework {

	use iTXTech\SimpleFramework\Console\Terminal;

	if(!defined("iTXTech\SimpleFramework\DISABLE_AUTO_INIT")){
		Initializer::loadSimpleFramework();
		Initializer::initClassLoader();

		//backward compatibility
		$classLoader = Initializer::getClassLoader();
	}

	abstract class Initializer{
		/** @var \ClassLoader */
		private static $classLoader;

		public static function loadSimpleFramework(string $phar = "SimpleFramework.phar"){
			$workingDir = __DIR__ . DIRECTORY_SEPARATOR;
			if(file_exists($workingDir . $phar)){
				define("iTXTech\SimpleFramework\PATH", "phar://" . $workingDir . $phar . DIRECTORY_SEPARATOR);
			}else{
				define("iTXTech\SimpleFramework\PATH", $workingDir);
			}
			require_once(\iTXTech\SimpleFramework\PATH . "src/iTXTech/SimpleFramework/Util/ClassLoader.php");
		}

		public static function initClassLoader(){
			self::$classLoader = new \ClassLoader();
			self::$classLoader->addPath(\iTXTech\SimpleFramework\PATH . "src");
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
	}
}
