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

	use iTXTech\SimpleFramework\Console\Logger;
	use iTXTech\SimpleFramework\Console\Terminal;

	date_default_timezone_set('Asia/Shanghai');

	if(\Phar::running(true) !== ""){
		@define('iTXTech\SimpleFramework\PATH', \Phar::running(true) . "/");
	}else{
		@define('iTXTech\SimpleFramework\PATH', \getcwd() . DIRECTORY_SEPARATOR);
	}

	require_once(PATH . "src/iTXTech/SimpleFramework/Util/ClassLoader.php");

	$classLoader = new \ClassLoader();
	$classLoader->addPath(PATH . "src");
	$classLoader->register(true);

	Terminal::init();

	ini_set("memory_limit", -1);
	define('iTXTech\SimpleFramework\START_TIME', microtime(true));

	if(version_compare("7.2", PHP_VERSION) > 0){
		Logger::error("You must use PHP >= 7.2");
		exit(1);
	}
	if(!extension_loaded("pthreads")){
		@define('iTXTech\SimpleFramework\SINGLE_THREAD', true);
	}else{
		@define('iTXTech\SimpleFramework\SINGLE_THREAD', false);
	}

	if(!SINGLE_THREAD){
		ThreadManager::init();
	}
	$framework = new Framework($classLoader);
	$framework->processCommandLineOptions($framework->processPreload($argv));
	$framework->start(true);

	if(!SINGLE_THREAD){
		Logger::info("Stopping other threads");

		foreach(ThreadManager::getInstance()->getAll() as $id => $thread){
			Logger::debug("Stopping " . (new \ReflectionClass($thread))->getShortName() . " thread");
			$thread->quit();
		}
	}

	echo "SimpleFramework is stopped." . PHP_EOL;
}
