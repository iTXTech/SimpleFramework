<?php

/**
 * SimpleFramework
 * The fast, light-weighted, easy-to-extend php framework.
 *
 * Some classes are based on project PocketMine-MP.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTXTech
 * @link https://itxtech.org
 */

namespace iTXTech\SimpleFramework {

	use iTXTech\SimpleFramework\Console\Logger;
	use iTXTech\SimpleFramework\Console\Terminal;
	use iTXTech\SimpleFramework\Util\Util;

	ini_set("memory_limit", -1);
	define('iTXTech\SimpleFramework\START_TIME', microtime(true));

	if(version_compare("7.2", PHP_VERSION) > 0){
		echo "You must use PHP >= 7.2" . PHP_EOL;
		exit(1);
	}
	if(!extension_loaded("pthreads")){
		@define('iTXTech\SimpleFramework\SINGLE_THREAD', true);
		echo "Unable to find the pthreads extension. " . PHP_EOL .
			"This program will run in single thread mode." . PHP_EOL;
	}else{
		@define('iTXTech\SimpleFramework\SINGLE_THREAD', false);
	}
	if(!extension_loaded("curl")){
		echo "Unable to find the cURL extension." . PHP_EOL;
		exit(1);
	}

	if(\Phar::running(true) !== ""){
		@define('iTXTech\SimpleFramework\PATH', \Phar::running(true) . "/");
	}else{
		@define('iTXTech\SimpleFramework\PATH', \getcwd() . DIRECTORY_SEPARATOR);
	}

	function getTrace($start = 0, $trace = null){
		if($trace === null){
			if(function_exists("xdebug_get_function_stack")){
				$trace = array_reverse(xdebug_get_function_stack());
			}else{
				$e = new \Exception();
				$trace = $e->getTrace();
			}
		}

		$messages = [];
		$j = 0;
		for($i = (int) $start; isset($trace[$i]); ++$i, ++$j){
			$params = "";
			if(isset($trace[$i]["args"]) or isset($trace[$i]["params"])){
				if(isset($trace[$i]["args"])){
					$args = $trace[$i]["args"];
				}else{
					$args = $trace[$i]["params"];
				}
				foreach($args as $name => $value){
					$params .= (is_object($value) ? get_class($value) . " object" : gettype($value) . " " . (is_array($value) ? "Array()" : Util::printable(@strval($value)))) . ", ";
				}
			}
			$messages[] = "#$j " . (isset($trace[$i]["file"]) ? $trace[$i]["file"] : "") . "(" . (isset($trace[$i]["line"]) ? $trace[$i]["line"] : "") . "): " . (isset($trace[$i]["class"]) ? $trace[$i]["class"] . (($trace[$i]["type"] === "dynamic" or $trace[$i]["type"] === "->") ? "->" : "::") : "") . $trace[$i]["function"] . "(" . Util::printable(substr($params, 0, -2)) . ")";
		}

		return $messages;
	}

	require_once(\iTXTech\SimpleFramework\PATH . "src/iTXTech/SimpleFramework/Util/ClassLoader.php");

	$classLoader = new \ClassLoader();
	$classLoader->addPath(\iTXTech\SimpleFramework\PATH . "src");
	$classLoader->register(true);

	date_default_timezone_set('Asia/Shanghai');

	Terminal::init();
	if(!\iTXTech\SimpleFramework\SINGLE_THREAD){
		ThreadManager::init();
	}
	new Framework($classLoader, $argv);

	if(!\iTXTech\SimpleFramework\SINGLE_THREAD){
		Logger::info("Stopping other threads");

		foreach(ThreadManager::getInstance()->getAll() as $id => $thread){
			Logger::debug("Stopping " . (new \ReflectionClass($thread))->getShortName() . " thread");
			$thread->quit();
		}
	}

	echo "SimpleFramework is stopped." . PHP_EOL;
}