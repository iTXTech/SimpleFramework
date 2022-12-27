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

namespace iTXTech\SimpleFramework;

use iTXTech\SimpleFramework\Scheduler\AsyncTask;

/**
 * This class must be extended by all custom threading classes
 */
abstract class Worker{

	/** @var \ClassLoader */
	protected $classLoader;

	protected $isKilled = false;

	protected $worker;

	public function __construct(){
		$this->worker = new System\ComponentModel\BackgroundWorker();
		$this->worker->WorkerSupportsCancellation = true;
	}

	public function getClassLoader(){
		return $this->classLoader;
	}

	public function setClassLoader(\ClassLoader $loader = null){
		$this->classLoader = $loader;
	}

	public function registerClassLoader(){
	}

	public function start(?int $options = PTHREADS_INHERIT_ALL){
		ThreadManager::getInstance()->add($this);

		if(!$this->worker->IsBusy){
			$this->worker->RunWorkerAsync();
		}
	}

	public function stack(AsyncTask $task){

	}

	/**
	 * Stops the thread using the best way possible. Try to stop it yourself before calling this.
	 */
	public function quit(){
		$this->isKilled = true;

		$this->worker->CancelAsync();

		ThreadManager::getInstance()->remove($this);
	}

	public function getThreadName(){
		return (new \ReflectionClass($this))->getShortName();
	}
}
