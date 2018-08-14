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

namespace iTXTech\SimpleFramework\Console;

use iTXTech\SimpleFramework\Thread;
use iTXTech\SimpleFramework\Util\Util;

class ConsoleReader extends Thread{
	/** @var \Threaded */
	protected $buffer;
	private $shutdown = false;
	private $stdin;

	public function __construct(){
		$this->stdin = fopen("php://stdin", "r");
		$this->buffer = new \Threaded;
		$this->start();
	}

	public function shutdown(){
		$this->shutdown = true;
	}

	public function quit(){
	}

	private function readLine(){
		$line = trim(fgets($this->stdin));
		if($line !== ""){
			$this->buffer[] = $line;
		}
	}

	/**
	 * Reads a line from console, if available. Returns null if not available
	 *
	 * @return string|null
	 */
	public function getLine(){
		if($this->buffer->count() !== 0){
			return $this->buffer->shift();
		}

		return null;
	}

	public function run(){
		while(!$this->shutdown){
			$r = [$this->stdin];
			$w = null;
			$e = null;
			if(stream_select($r, $w, $e, 0, 200000) > 0){
				// PHP on Windows sucks
				if(feof($this->stdin)){
					if(Util::getOS() == "win"){
						$this->stdin = fopen("php://stdin", "r");
						if(!is_resource($this->stdin)){
							break;
						}
					}else{
						break;
					}
				}
				$this->readLine();
			}
		}
	}

	public function getThreadName(){
		return "Console";
	}
}
