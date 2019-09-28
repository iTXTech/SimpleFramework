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

	if(!defined("SF_LOADER_AUTO_INIT") or SF_LOADER_AUTO_INIT){
		Initializer::loadSimpleFramework();
		Initializer::initClassLoader();

		//backward compatibility
		$classLoader = Initializer::getClassLoader();
	}

	abstract class Initializer{
		/** @var \ClassLoader */
		private static $classLoader;

		public static function loadSimpleFramework(string $phar = "SimpleFramework.phar",
		                                           string $workingDir = __DIR__){
			$workingDir .= DIRECTORY_SEPARATOR;
			if(\Phar::running(true) !== ""
				and (new \Phar(\Phar::running()))->getMetadata()["name"] == "SimpleFramework"){
				define("iTXTech\SimpleFramework\PATH", \Phar::running(true) . "/");
			}elseif(file_exists($phar)){
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
