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

namespace iTXTech\SimpleFramework\Scheduler;

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Worker;

class AsyncWorker extends Worker{

	private $id;

	public function __construct($id){
		$this->id = $id;
	}

	public function run(){
		$this->registerClassLoader();
		gc_enable();
		ini_set("memory_limit", -1);

		global $store;
		$store = [];
	}

	public function handleException(\Throwable $e){
		Logger::logException($e);
	}

	public function getThreadName(){
		return "Asynchronous Worker #" . $this->id;
	}
}
