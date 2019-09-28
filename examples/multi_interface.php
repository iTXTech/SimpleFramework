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


require_once "../autoload.php";

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Framework;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Scheduler\AsyncTask;
use iTXTech\SimpleFramework\Scheduler\OnCompletionListener;
use iTXTech\SimpleFramework\Scheduler\Scheduler;
use iTXTech\SimpleFramework\Util\Curl\Curl;
use iTXTech\SimpleFramework\Util\Curl\InterfaceSelector;

Logger::$logLevel = Logger::DEBUG;
Initializer::initTerminal(true);
Initializer::setSingleThread(false);

//change to your own address or interface
InterfaceSelector::registerInterface("111.212.238.170", 1);
InterfaceSelector::registerInterface("192.168.2.165", 1);

$scheduler = new Scheduler($classLoader, new class implements OnCompletionListener{
}, 64);
for($i = 0; $i < 1000; $i++){
	Logger::info($i);
	$scheduler->scheduleAsyncTask(new class extends AsyncTask{
		public function onRun(){
			$curl = Curl::newInstance();
			$curl->setUrl("https://www.baidu.com")
				->exec();
		}

		public function onCompletion(OnCompletionListener $listener){
		}
	});
}

while(true){
	$scheduler->mainThreadHeartbeat(1);
	usleep(Framework::getTickInterval());
}
