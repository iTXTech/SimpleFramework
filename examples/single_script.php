<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2021 iTX Technologies
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

/*
 * This example shows how to write a single script with SimpleFramework APIs
 * In this mode, you have to initialize all APIs which you want to use
 *
 * This mode is Composer compatible!
 */

require_once "../autoload.php";

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleFramework\Framework;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Scheduler\AsyncTask;
use iTXTech\SimpleFramework\Scheduler\OnCompletionListener;
use iTXTech\SimpleFramework\Scheduler\Scheduler;

Initializer::setSingleThread(false);
Initializer::initTerminal(true);

Logger::info("Starting...");
$scheduler = new Scheduler($classLoader, new class implements OnCompletionListener{
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
	usleep(Framework::getTickInterval());
}
