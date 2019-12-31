<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2020 iTX Technologies
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

namespace iTXTech\SimpleFramework\Module;

use iTXTech\SimpleFramework\Thread;

class HotPatchThread extends Thread{
	private $file;
	private $class;
	private $method;
	private $loader;
	private $path;
	private $code;

	public function __construct(string $class, string $method, \ClassLoader $loader){
		$this->class = $class;
		$this->method = $method;
		$this->file = (new \ReflectionClass($class))->getFileName();
		$this->loader = serialize($loader);
		$this->path = (new \ReflectionClass($loader))->getFileName();
	}

	public function run(){
		require_once $this->path;
		$classLoader = unserialize($this->loader);
		$classLoader->register(true);
		require_once $this->file;
		$method = (new \ReflectionClass($this->class))->getMethod($this->method);
		$this->code = implode("", array_slice(file($this->file), $method->getStartLine() - 1,
			$method->getEndLine() - $method->getStartLine() + 1));
	}

	public function getCode(){
		return $this->code;
	}
}
