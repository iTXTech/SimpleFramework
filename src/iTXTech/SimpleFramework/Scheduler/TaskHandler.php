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

namespace iTXTech\SimpleFramework\Scheduler;

class TaskHandler{

	/** @var Task */
	protected $task;

	/** @var int */
	protected $taskId;

	/** @var int */
	protected $delay;

	/** @var int */
	protected $period;

	/** @var int */
	protected $nextRun;

	/** @var bool */
	protected $cancelled = false;

	/**
	 * @param Task   $task
	 * @param int    $taskId
	 * @param int    $delay
	 * @param int    $period
	 */
	public function __construct(Task $task, $taskId, $delay = -1, $period = -1){
		$this->task = $task;
		$this->taskId = $taskId;
		$this->delay = $delay;
		$this->period = $period;
	}

	/**
	 * @return bool
	 */
	public function isCancelled(){
		return $this->cancelled === true;
	}

	/**
	 * @return int
	 */
	public function getNextRun(){
		return $this->nextRun;
	}

	/**
	 * @param int $ticks
	 */
	public function setNextRun($ticks){
		$this->nextRun = $ticks;
	}

	/**
	 * @return int
	 */
	public function getTaskId(){
		return $this->taskId;
	}

	/**
	 * @return Task
	 */
	public function getTask(){
		return $this->task;
	}

	/**
	 * @return int
	 */
	public function getDelay(){
		return $this->delay;
	}

	/**
	 * @return bool
	 */
	public function isDelayed(){
		return $this->delay > 0;
	}

	/**
	 * @return bool
	 */
	public function isRepeating(){
		return $this->period > 0;
	}

	/**
	 * @return int
	 */
	public function getPeriod(){
		return $this->period;
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 */
	public function cancel(){
		if(!$this->isCancelled()){
			$this->task->onCancel();
		}
		$this->remove();
	}

	public function remove(){
		$this->cancelled = true;
		$this->task->setHandler(null);
	}

	/**
	 * @param int $currentTick
	 */
	public function run($currentTick){
		$this->task->onRun($currentTick);
	}

	/**
	 * @return string
	 */
	public function getTaskName(){
		return get_class($this->task);
	}
}
