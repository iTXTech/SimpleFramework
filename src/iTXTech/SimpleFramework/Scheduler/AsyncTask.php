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

/**
 * Class used to run async tasks in other threads.
 */
abstract class AsyncTask extends \Threaded implements \Collectable{

	/** @var AsyncWorker $worker */
	public $worker = null;

	private $result = null;
	private $serialized = false;
	private $cancelRun = false;
	/** @var int */
	private $taskId = null;

	private $crashed = false;

	private $isGarbage = false;

	private $isFinished = false;

	public function isGarbage() : bool{
		return $this->isGarbage;
	}

	public function setGarbage(){
		$this->isGarbage = true;
	}

	public function isFinished() : bool{
		return $this->isFinished;
	}

	public function run(){
		date_default_timezone_set('Asia/Shanghai');
		$this->result = null;
		$this->isGarbage = false;

		if($this->cancelRun !== true){
			try{
				$this->onRun();
			}catch(\Throwable $e){
				$this->crashed = true;
				$this->worker->handleException($e);
			}
		}

		$this->isFinished = true;
		//$this->setGarbage();
	}

	public function isCrashed(){
		return $this->crashed;
	}

	/**
	 * @return mixed
	 */
	public function getResult(){
		return $this->serialized ? unserialize($this->result) : $this->result;
	}

	public function cancelRun(){
		$this->cancelRun = true;
	}

	public function hasCancelledRun(){
		return $this->cancelRun === true;
	}

	/**
	 * @return bool
	 */
	public function hasResult(){
		return $this->result !== null;
	}

	/**
	 * @param mixed $result
	 * @param bool  $serialize
	 */
	public function setResult($result, $serialize = true){
		$this->result = $serialize ? serialize($result) : $result;
		$this->serialized = $serialize;
	}

	public function setTaskId($taskId){
		$this->taskId = $taskId;
	}

	public function getTaskId(){
		return $this->taskId;
	}

	/**
	 * Gets something into the local thread store.
	 * You have to initialize this in some way from the task on run
	 *
	 * @param string $identifier
	 * @return mixed
	 */
	public function getFromThreadStore($identifier){
		global $store;
		return $this->isGarbage() ? null : $store[$identifier];
	}

	/**
	 * Saves something into the local thread store.
	 * This might get deleted at any moment.
	 *
	 * @param string $identifier
	 * @param mixed  $value
	 */
	public function saveToThreadStore($identifier, $value){
		global $store;
		if(!$this->isGarbage()){
			$store[$identifier] = $value;
		}
	}

	/**
	 * Actions to execute when run
	 *
	 * @return void
	 */
	public abstract function onRun();

	/**
	 * Actions to execute when completed (on main thread)
	 * Implement this if you want to handle the data in your AsyncTask after it has been processed
	 *
	 * @param OnCompletionListener $listener
	 *
	 * @return void
	 */
	public function onCompletion(OnCompletionListener $listener){

	}

	public function cleanObject(){
		foreach($this as $p => $v){
			if(!($v instanceof \Threaded) and !in_array($p, ["isFinished", "isGarbage", "cancelRun"])){
				$this->{$p} = null;
			}
		}
	}

}
