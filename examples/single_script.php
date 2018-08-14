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

/*
 * This example shows how to write a single script with SimpleFramework APIs
 * In this mode, you have to initialize all APIs which you want to use
 *
 * This mode is Composer compatible!
 */

require_once "../autoload.php";

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Terminal;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleFramework\Framework;
use iTXTech\SimpleFramework\Scheduler\AsyncTask;
use iTXTech\SimpleFramework\Scheduler\OnCompletionListener;
use iTXTech\SimpleFramework\Scheduler\ServerScheduler;
use iTXTech\SimpleFramework\ThreadManager;

@define('iTXTech\SimpleFramework\SINGLE_THREAD', false);//multi-threading!

//initialize utilities
Terminal::$formattingCodes = true;
Terminal::init();
ThreadManager::init();
Logger::info("Starting...");
$scheduler = new ServerScheduler($classLoader, new class implements OnCompletionListener{
	public function test(){
		Logger::info("Hello from OnCompleteListener! " . mt_rand(0, 10000));
	}
}, 16);
for($i = 0; $i < 100; $i++){
	Logger::info($i);
	$scheduler->scheduleAsyncTask(new class extends AsyncTask{
		public function onRun(){
			Logger::info(TextFormat::GREEN . "AsyncTask is running!");
			usleep(mt_rand(100000, 1000000));
			Logger::info(TextFormat::RED . "AsyncTask is completed!");
		}

		public function onCompletion(OnCompletionListener $listener){
			$listener->test();
		}
	});
}

while(true){
	$scheduler->mainThreadHeartbeat(1);
	usleep(Framework::$usleep);
}