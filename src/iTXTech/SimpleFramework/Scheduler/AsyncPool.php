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

class AsyncPool{

	/** @var \ClassLoader */
	private $classloader;

	/** @var OnCompletionListener */
	private $listener;

	protected $size;

	/** @var AsyncTask[] */
	private $tasks = [];
	/** @var int[] */
	private $taskWorkers = [];

	/** @var AsyncWorker[] */
	private $workers = [];
	/** @var int[] */
	private $workerUsage = [];

	public function __construct(\ClassLoader $classLoader, OnCompletionListener $listener, $size){
		$this->classloader = $classLoader;
		$this->listener = $listener;
		$this->size = (int) $size;

		for($i = 0; $i < $this->size; ++$i){
			$this->workerUsage[$i] = 0;
			$this->workers[$i] = new AsyncWorker($i + 1);
			$this->workers[$i]->setClassLoader($classLoader);
			$this->workers[$i]->start();
		}
	}

	public function getWorkers(){
		return $this->workers;
	}

	public function getSize(){
		return $this->size;
	}

	public function increaseSize($newSize){
		$newSize = (int) $newSize;
		if($newSize > $this->size){
			for($i = $this->size; $i < $newSize; ++$i){
				$this->workerUsage[$i] = 0;
				$this->workers[$i] = new AsyncWorker($i + 1);
				$this->workers[$i]->setClassLoader($this->classloader);
				$this->workers[$i]->start();
			}
			$this->size = $newSize;
		}
	}

	public function submitTaskToWorker(AsyncTask $task, $worker){
		if(isset($this->tasks[$task->getTaskId()]) or $task->isGarbage()){
			return;
		}

		$worker = (int) $worker;
		if($worker < 0 or $worker >= $this->size){
			throw new \InvalidArgumentException("Invalid worker $worker");
		}

		$this->tasks[$task->getTaskId()] = $task;

		$this->workers[$worker]->stack($task);
		$this->workerUsage[$worker]++;
		$this->taskWorkers[$task->getTaskId()] = $worker;
	}

	public function submitTask(AsyncTask $task){
		if(isset($this->tasks[$task->getTaskId()]) or $task->isGarbage()){
			return;
		}

		$selectedWorker = mt_rand(0, $this->size - 1);
		$selectedTasks = $this->workerUsage[$selectedWorker];
		for($i = 0; $i < $this->size; ++$i){
			if($this->workerUsage[$i] < $selectedTasks){
				$selectedWorker = $i;
				$selectedTasks = $this->workerUsage[$i];
			}
		}

		$this->submitTaskToWorker($task, $selectedWorker);
	}

	private function removeTask(AsyncTask $task, $force = false){
		$task->setGarbage();

		if(isset($this->taskWorkers[$task->getTaskId()])){
			if(!$force and ($task->isRunning() or !$task->isGarbage())){
				return;
			}
			$this->workerUsage[$this->taskWorkers[$task->getTaskId()]]--;
		}

		unset($this->tasks[$task->getTaskId()]);
		unset($this->taskWorkers[$task->getTaskId()]);

		$task->cleanObject();
	}

	public function removeTasks(){
		do{
			foreach($this->tasks as $task){
				$task->cancelRun();
				$this->removeTask($task);
			}
		}while(count($this->tasks) > 0);

		for($i = 0; $i < $this->size; ++$i){
			$this->workerUsage[$i] = 0;
		}

		$this->taskWorkers = [];
		$this->tasks = [];
	}

	public function collectTasks(){
		foreach($this->tasks as $task){
			if($task->isFinished() and !$task->isRunning() and !$task->isCrashed()){

				if(!$task->hasCancelledRun()){
					$task->onCompletion($this->listener);
				}

				$this->removeTask($task);
			}elseif($task->isTerminated() or $task->isCrashed()){
				Logger::critical("Could not execute asynchronous task " . (new \ReflectionClass($task))->getShortName() . ": Task crashed");
				$this->removeTask($task, true);
			}
		}
	}
}
