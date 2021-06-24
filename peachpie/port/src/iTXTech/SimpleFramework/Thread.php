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

namespace iTXTech\SimpleFramework;

/**
 * This class must be extended by all custom threading classes
 */
abstract class Thread{

	/** @var \ClassLoader */
	protected $classLoader;
	protected $isKilled = false;

	protected $thread;

	public function __construct(){
        //	$this->thread = new \System\Threading\Thread(\System\Func<void>(fn() => $this->run()));
    }

	public function getClassLoader(){
		return $this->classLoader;
	}

	public function setClassLoader(\ClassLoader $loader = null){
		$this->classLoader = $loader;
	}

    public function registerClassLoader()
    {
    }

    public function start(?int $options = \PTHREADS_INHERIT_ALL)
    {
        ThreadManager::getInstance()->add($this);
        $this->thread->start();
    }

    public abstract function run();

    public function isRunning(): bool
    {
        return $this->thread->ThreadState->Equals(\System\Threading\ThreadState::Running);
    }

    public function synchronized(callable $block)
    {

    }

    public function wait(int $ms)
    {

    }

    /**
	 * Stops the thread using the best way possible. Try to stop it yourself before calling this.
	 */
	public function quit(){
		$this->isKilled = true;

		$this->thread->join();

		ThreadManager::getInstance()->remove($this);
	}

	public function getThreadName(){
		return (new \ReflectionClass($this))->getShortName();
	}
}
