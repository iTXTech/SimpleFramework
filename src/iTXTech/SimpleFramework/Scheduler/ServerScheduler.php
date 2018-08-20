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

/**
 * Task scheduling related classes
 */
namespace iTXTech\SimpleFramework\Scheduler;

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Util\ReversePriorityQueue;

class ServerScheduler{
	/**
	 * @var ReversePriorityQueue<Task>
	 */
	protected $queue;

	/**
	 * @var TaskHandler[]
	 */
	protected $tasks = [];

	/** @var AsyncPool */
	protected $asyncPool;

	/** @var int */
	private $ids = 1;

	/** @var int */
	protected $currentTick = 0;

	private static $singleThread = false;

	public function __construct(\ClassLoader $classLoader, OnCompletionListener $listener, int $workers){
		$this->queue = new ReversePriorityQueue();
		if(\iTXTech\SimpleFramework\SINGLE_THREAD){
			self::$singleThread = true;
		}else{
			$this->asyncPool = new AsyncPool($classLoader, $listener, $workers);
		}
	}

	public function getAsyncPool(): AsyncPool{
		return $this->asyncPool;
	}

	/**
	 * @param Task $task
	 *
	 * @return null|TaskHandler
	 */
	public function scheduleTask(Task $task){
		return $this->addTask($task, -1, -1);
	}

	/**
	 * Submits an asynchronous task to the Worker Pool
	 *
	 * @param AsyncTask $task
	 *
	 * @return void
	 */
	public function scheduleAsyncTask(AsyncTask $task){
		if(!self::$singleThread){
			$id = $this->nextId();
			$task->setTaskId($id);
			$this->asyncPool->submitTask($task);
		}
	}

	/**
	 * Submits an asynchronous task to a specific Worker in the Pool
	 *
	 * @param AsyncTask $task
	 * @param int       $worker
	 *
	 * @return void
	 */
	public function scheduleAsyncTaskToWorker(AsyncTask $task, $worker){
		if(!self::$singleThread){
			$id = $this->nextId();
			$task->setTaskId($id);
			$this->asyncPool->submitTaskToWorker($task, $worker);
		}
	}

	public function getAsyncTaskPoolSize(){
		return self::$singleThread ? -1 : $this->asyncPool->getSize();
	}

	public function increaseAsyncTaskPoolSize($newSize){
		if(!self::$singleThread){
			$this->asyncPool->increaseSize($newSize);
		}
	}

	/**
	 * @param Task $task
	 * @param int  $delay
	 *
	 * @return null|TaskHandler
	 */
	public function scheduleDelayedTask(Task $task, $delay){
		return $this->addTask($task, (int) $delay, -1);
	}

	/**
	 * @param Task $task
	 * @param int  $period
	 *
	 * @return null|TaskHandler
	 */
	public function scheduleRepeatingTask(Task $task, $period){
		return $this->addTask($task, -1, (int) $period);
	}

	/**
	 * @param Task $task
	 * @param int  $delay
	 * @param int  $period
	 *
	 * @return null|TaskHandler
	 */
	public function scheduleDelayedRepeatingTask(Task $task, $delay, $period){
		return $this->addTask($task, (int) $delay, (int) $period);
	}

	/**
	 * @param int $taskId
	 */
	public function cancelTask($taskId){
		if($taskId !== null and isset($this->tasks[$taskId])){
			$this->tasks[$taskId]->cancel();
			unset($this->tasks[$taskId]);
		}
	}

	public function cancelAllTasks(){
		foreach($this->tasks as $task){
			$task->cancel();
		}
		$this->tasks = [];
		$this->asyncPool->removeTasks();
		while(!$this->queue->isEmpty()){
			$this->queue->extract();
		}
		$this->ids = 1;
	}

	/**
	 * @param int $taskId
	 *
	 * @return bool
	 */
	public function isQueued($taskId){
		return isset($this->tasks[$taskId]);
	}

	/**
	 * @param Task $task
	 * @param      $delay
	 * @param      $period
	 *
	 * @return null|TaskHandler
	 */
	private function addTask(Task $task, $delay, $period){

		if($delay <= 0){
			$delay = -1;
		}

		if($period <= -1){
			$period = -1;
		}elseif($period < 1){
			$period = 1;
		}

		return $this->handle(new TaskHandler($task, $this->nextId(), $delay, $period));
	}

	private function handle(TaskHandler $handler){
		if($handler->isDelayed()){
			$nextRun = $this->currentTick + $handler->getDelay();
		}else{
			$nextRun = $this->currentTick;
		}

		$handler->setNextRun($nextRun);
		$this->tasks[$handler->getTaskId()] = $handler;
		$this->queue->insert($handler, $nextRun);

		return $handler;
	}

	/**
	 * @param int $currentTick
	 */
	public function mainThreadHeartbeat($currentTick){
		$this->currentTick = $currentTick;
		while($this->isReady($this->currentTick)){
			/** @var TaskHandler $task */
			$task = $this->queue->extract();
			if($task->isCancelled()){
				unset($this->tasks[$task->getTaskId()]);
				continue;
			}else{
				try{
					$task->run($this->currentTick);
				}catch(\Throwable $e){
					Logger::critical("Could not execute task " . $task->getTaskName() . ": " . $e->getMessage());
					Logger::logException($e);
				}
			}
			if($task->isRepeating()){
				$task->setNextRun($this->currentTick + $task->getPeriod());
				$this->queue->insert($task, $this->currentTick + $task->getPeriod());
			}else{
				$task->remove();
				unset($this->tasks[$task->getTaskId()]);
			}
		}

		if(!self::$singleThread){
			$this->asyncPool->collectTasks();
		}
	}

	private function isReady($currentTicks){
		return count($this->tasks) > 0 and $this->queue->current()->getNextRun() <= $currentTicks;
	}

	/**
	 * @return int
	 */
	private function nextId(){
		return $this->ids++;
	}

}
