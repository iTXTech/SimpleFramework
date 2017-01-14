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
 * @author PeratX
 */

namespace sf {

	use sf\console\Logger;
	use sf\console\Terminal;

	ini_set("memory_limit", -1);

	if(version_compare("7.0", PHP_VERSION) > 0){
		echo "You must use PHP >= 7.0" . PHP_EOL;
		exit(1);
	}
	if(!extension_loaded("pthreads")){
		echo "Unable to find the pthreads extension" . PHP_EOL;
		exit(1);
	}
	if(!extension_loaded("curl")){
		echo "Unable to find the cURL extension." . PHP_EOL;
		exit(1);
	}

	if(\Phar::running(true) !== ""){
		@define('sf\PATH', \Phar::running(true) . "/");
	}else{
		@define('sf\PATH', \getcwd() . DIRECTORY_SEPARATOR);
	}

	require_once(\sf\PATH . "src/sf/util/ClassLoader.php");

	$classLoader = new \ClassLoader();
	$classLoader->addPath(\sf\PATH . "src");
	$classLoader->register(true);

	date_default_timezone_set('Asia/Shanghai');

	Terminal::init();
	ThreadManager::init();
	new Framework($classLoader, $argv);

	Logger::info("Stopping other threads");

	foreach(ThreadManager::getInstance()->getAll() as $id => $thread){
		Logger::debug("Stopping " . (new \ReflectionClass($thread))->getShortName() . " thread");
		$thread->quit();
	}

	echo "SimpleFramework is stopped." . PHP_EOL;
}