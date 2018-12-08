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
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace iTXTech\SimpleFramework\Console;

use iTXTech\SimpleFramework\Thread;

class ConsoleReader extends Thread{
	public const TYPE_STREAM = 1;
	public const TYPE_PIPED = 2;

	/** @var resource */
	private static $stdin;

	/** @var \Threaded */
	protected $buffer;
	private $shutdown = false;
	private $type = self::TYPE_STREAM;


	public function __construct(){
		$this->buffer = new \Threaded;
		$this->start();
	}

	public function shutdown(){
		$this->shutdown = true;
	}

	public function quit(){
		$wait = microtime(true) + 0.5;
		while(microtime(true) < $wait){
			if($this->isRunning()){
				usleep(100000);
			}else{
				parent::quit();
				return;
			}
		}
	}

	private function initStdin(){
		if(is_resource(self::$stdin)){
			fclose(self::$stdin);
		}

		self::$stdin = fopen("php://stdin", "r");
		if($this->isPipe(self::$stdin)){
			$this->type = self::TYPE_PIPED;
		}else{
			$this->type = self::TYPE_STREAM;
		}
	}

	/**
	 * Checks if the specified stream is a FIFO pipe.
	 *
	 * @param resource $stream
	 *
	 * @return bool
	 */
	private function isPipe($stream) : bool{
		return is_resource($stream) and (!stream_isatty($stream) or ((fstat($stream)["mode"] & 0170000) === 0010000));
	}

	/**
	 * Reads a line from the console and adds it to the buffer. This method may block the thread.
	 *
	 * @return bool if the main execution should continue reading lines
	 */
	private function readLine() : bool{
		$line = "";
		if(!is_resource(self::$stdin)){
			$this->initStdin();
		}

		switch($this->type){
			/** @noinspection PhpMissingBreakStatementInspection */
			case self::TYPE_STREAM:
				//stream_select doesn't work on piped streams for some reason
				$r = [self::$stdin];
				if(($count = stream_select($r, $w, $e, 0, 200000)) === 0){ //nothing changed in 200000 microseconds
					return true;
				}elseif($count === false){ //stream error
					$this->initStdin();
				}

			case self::TYPE_PIPED:
				if(($raw = fgets(self::$stdin)) === false){ //broken pipe or EOF
					$this->initStdin();
					$this->synchronized(function(){
						$this->wait(200000);
					}); //prevent CPU waste if it's end of pipe
					return true; //loop back round
				}

				$line = trim($raw);
				break;
		}

		if($line !== ""){
			$this->buffer[] = preg_replace("#\\x1b\\x5b([^\\x1b]*\\x7e|[\\x40-\\x50])#", "", $line);
		}

		return true;
	}

	/**
	 * Reads a line from console, if available. Returns null if not available
	 *
	 * @return string|null
	 */
	public function getLine(){
		if($this->buffer->count() !== 0){
			return (string) $this->buffer->shift();
		}

		return null;
	}

	public function run(){
		$this->registerClassLoader();
		$this->initStdin();
		while(!$this->shutdown and $this->readLine()) ;
		fclose(self::$stdin);
	}

	public function getThreadName() : string{
		return "Console";
	}
}
