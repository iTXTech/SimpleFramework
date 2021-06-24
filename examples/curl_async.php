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

require_once "../autoload.php";

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleFramework\Framework;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Scheduler\OnCompletionListener;
use iTXTech\SimpleFramework\Scheduler\Scheduler;
use iTXTech\SimpleFramework\Util\Curl\Callback;
use iTXTech\SimpleFramework\Util\Curl\Curl;
use iTXTech\SimpleFramework\Util\Curl\Response;

Initializer::setSingleThread(false);
$scheduler = new Scheduler(Initializer::getClassLoader(), new class implements OnCompletionListener{
}, 2);

$curl = Curl::newInstance();
$curl->setUrl("https://github.com")
	->setHeader("Expect")
	->setUserAgent(Framework::PROG_NAME . " " . Framework::PROG_VERSION)
	->execAsync($scheduler, new class implements Callback{
		public function onResponse(Response $resp){
			//called on main thread
			if($resp->isSuccessful()){
				Logger::info("Code: " . $resp->getHttpCode());
				Logger::info("Got " . count($resp->getCookies()) . " cookies");
				Logger::info("Got " . count($resp->getHeaders()) . " headers");
				Logger::info("Body len: " . strlen($resp->getBody()));
			}else{
				Logger::error("Cannot reach target, errno: " . $resp->getErrno());
			}
			Logger::info(TextFormat::GREEN . "Request completed. CTRL-C to exit.");
		}
	});

while(true){
	//keep heartbeat
	$scheduler->mainThreadHeartbeat(0);
	usleep(Framework::getTickInterval());
}
