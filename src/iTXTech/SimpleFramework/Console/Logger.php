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

use iTXTech\SimpleFramework\Util\Util;

class Logger{
	const EMERGENCY = "emergency";
	const ALERT = "alert";
	const CRITICAL = "critical";
	const ERROR = "error";
	const WARNING = "warning";
	const NOTICE = "notice";
	const INFO = "info";
	const DEBUG = "debug";

	public static $noColor = false;
	public static $fullDisplay = true;
	public static $noOutput = false;

	private static $logfile = "";


	public static function setLogFile(string $logfile){
		if($logfile != ""){
			touch($logfile);
		}
		self::$logfile = $logfile;
	}

	public static function emergency($message, $name = "EMERGENCY"){
		self::send($message, $name, TextFormat::RED);
	}

	public static function alert($message, $name = "ALERT"){
		self::send($message, $name, TextFormat::RED);
	}

	public static function critical($message, $name = "CRITICAL"){
		self::send($message, $name, TextFormat::RED);
	}

	public static function error($message, $name = "ERROR"){
		self::send($message, $name, TextFormat::DARK_RED);
	}

	public static function warning($message, $name = "WARNING"){
		self::send($message, $name, TextFormat::YELLOW);
	}

	public static function notice($message, $name = "NOTICE"){
		self::send($message, $name, TextFormat::AQUA);
	}

	public static function info($message, $name = "INFO"){
		self::send($message, $name, TextFormat::WHITE);
	}

	public static function debug($message, $name = "DEBUG"){
		self::send($message, $name, TextFormat::GRAY);
	}

	public static function logException(\Throwable $e){
		$trace = $e->getTrace();
		$errstr = $e->getMessage();
		$errfile = $e->getFile();
		$errno = $e->getCode();
		$errline = $e->getLine();

		$errorConversion = [
			0 => "EXCEPTION",
			E_ERROR => "E_ERROR",
			E_WARNING => "E_WARNING",
			E_PARSE => "E_PARSE",
			E_NOTICE => "E_NOTICE",
			E_CORE_ERROR => "E_CORE_ERROR",
			E_CORE_WARNING => "E_CORE_WARNING",
			E_COMPILE_ERROR => "E_COMPILE_ERROR",
			E_COMPILE_WARNING => "E_COMPILE_WARNING",
			E_USER_ERROR => "E_USER_ERROR",
			E_USER_WARNING => "E_USER_WARNING",
			E_USER_NOTICE => "E_USER_NOTICE",
			E_STRICT => "E_STRICT",
			E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
			E_DEPRECATED => "E_DEPRECATED",
			E_USER_DEPRECATED => "E_USER_DEPRECATED",
		];
		if($errno === 0){
			$type = self::CRITICAL;
		}else{
			$type = ($errno === E_ERROR or $errno === E_USER_ERROR) ? self::ERROR : (($errno === E_USER_WARNING or $errno === E_WARNING) ? self::WARNING : self::NOTICE);
		}
		$errno = isset($errorConversion[$errno]) ? $errorConversion[$errno] : $errno;
		if(($pos = strpos($errstr, "\n")) !== false){
			$errstr = substr($errstr, 0, $pos);
		}
		self::log($type, get_class($e) . ": \"$errstr\" ($errno) in \"$errfile\" at line $errline");
		foreach(Util::getTrace(0, $trace) as $i => $line){
			self::debug($line);
		}
	}

	public static function log($level, $message){
		switch($level){
			case self::EMERGENCY:
				self::emergency($message);
				break;
			case self::ALERT:
				self::alert($message);
				break;
			case self::CRITICAL:
				self::critical($message);
				break;
			case self::ERROR:
				self::error($message);
				break;
			case self::WARNING:
				self::warning($message);
				break;
			case self::NOTICE:
				self::notice($message);
				break;
			case self::INFO:
				self::info($message);
				break;
			case self::DEBUG:
				self::debug($message);
				break;
		}
	}

	protected static function send($message, $prefix, $color){
		if(self::$fullDisplay){
			$now = time();
			$class = @end(explode('\\', debug_backtrace()[2]['class']));
			if(strlen($class) > 20){
				$class = substr($class, 0, 20);
			}
			$class = $class == "" ? "Console" : $class;
			$message = TextFormat::toANSI(TextFormat::AQUA . "[" . date("G:i:s", $now) . "] " . TextFormat::RESET . $color . $class . "/" . $prefix . ">" . " " . $message . TextFormat::RESET);
		}else{
			$message = TextFormat::toANSI($message);
		}
		$cleanMessage = TextFormat::clean($message);

		if(self::$noOutput){
			return;
		}
		if(!Terminal::hasFormattingCodes() or self::$noColor){
			echo $cleanMessage . PHP_EOL;
		}else{
			echo $message . PHP_EOL;
		}

		if(self::$logfile != ""){
			file_put_contents(self::$logfile, $cleanMessage . PHP_EOL, FILE_APPEND);
		}
	}
}
