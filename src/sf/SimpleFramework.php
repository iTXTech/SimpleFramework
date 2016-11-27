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

	if(\Phar::running(true) !== ""){
		@define('sf\PATH', \Phar::running(true) . "/");
	}else{
		@define('sf\PATH', \getcwd() . DIRECTORY_SEPARATOR);
	}

	require_once(\sf\PATH . "src/sf/util/ClassLoader.php");

	$classLoader = new \ClassLoader();
	$classLoader->addPath(\sf\PATH . "src");
	$classLoader->register(true);

	Terminal::init();
	ThreadManager::init();
	new Framework($classLoader, $argv);

	Logger::info("Stopping other threads");

	foreach(ThreadManager::getInstance()->getAll() as $id => $thread){
		Logger::debug("Stopping " . (new \ReflectionClass($thread))->getShortName() . " thread");
		$thread->quit();
	}

	Logger::info("SimpleFramework is stopped.");
}