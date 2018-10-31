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

use Swoole\Channel;
use Swoole\Process;

abstract class SwooleLoggerHandler extends LoggerHandler{
	/** @var Process */
	private static $proc;
	/** @var Channel */
	private static $channel;

	public static function shutdown(){
		self::$proc->close();
	}

	public static function init(){
		$channel = new Channel(1024 * 1024 * 32);
		self::$channel = $channel;
		self::$proc = new Process(function(Process $process) use ($channel){
			go(function() use ($channel){
				while(true){
					while(($line = $channel->pop()) !== false){
						echo $line . PHP_EOL;
					}
					\co::sleep(0.01);
				}
			});
		});
		self::$proc->start();
	}

	public static function send(string $message, string $prefix, string $color){
		$now = time();
		$class = @end(explode('\\', debug_backtrace()[2]['class']));
		if(strlen($class) > 20){
			$class = substr($class, 0, 20);
		}
		$class = $class == "" ? "Console" : $class;
		$message = TextFormat::toANSI(TextFormat::AQUA . "[" . date("G:i:s", $now) . "] " .
			TextFormat::RESET . $color . $class . "/" . $prefix . ">" . " " . $message . TextFormat::RESET);

		$cleanMessage = TextFormat::clean($message);

		if(!Terminal::hasFormattingCodes()){
			self::$channel->push($cleanMessage);
		}else{
			self::$channel->push($message);
		}
	}
}
