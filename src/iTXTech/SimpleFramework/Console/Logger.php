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

namespace iTXTech\SimpleFramework\Console;

use iTXTech\SimpleFramework\Util\Util;

abstract class Logger implements LoggerHandler{
	public const ERROR = 3;
	public const WARNING = 2;
	public const INFO = 1;
	public const DEBUG = 0;

	public const PREFIX = [
		self::DEBUG => "DEBUG",
		self::INFO => "INFO",
		self::WARNING => "WARNING",
		self::ERROR => "ERROR"
	];

	public static $logLevel = self::DEBUG;
	public static $hasPrefix = true;
	public static $disableOutput = false;
	public static $disableClass = false;

	private static $logfile = "";
	/** @var LoggerHandler */
	private static $loggerHandler = Logger::class;

	public static function setLoggerHandler(string $class) : bool{
		if(is_a($class, LoggerHandler::class, true)){
			self::$loggerHandler = $class;
			return true;
		}
		return false;
	}

	public static function setLogFile(string $logfile){
		if($logfile != ""){
			touch($logfile);
		}
		self::$logfile = $logfile;
	}

	public static function error(string $message){
		self::send($message, self::ERROR, TextFormat::DARK_RED);
	}

	public static function warning(string $message){
		self::send($message, self::WARNING, TextFormat::YELLOW);
	}

	public static function info(string $message){
		self::send($message, self::INFO, TextFormat::WHITE);
	}

	public static function debug(string $message){
		self::send($message, self::DEBUG, TextFormat::GRAY);
	}

	public static function errorExceptionHandler(int $severity, string $message, string $file, int $line) : bool{
		if((error_reporting() & $severity) !== 0){
			self::logException(new \ErrorException($message, 0, $severity, $file, $line));
		}
		return true;
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
			$type = self::ERROR;
		}else{
			$type = ($errno === E_ERROR or $errno === E_USER_ERROR) ? self::ERROR : self::WARNING;
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
			case self::ERROR:
				self::error($message);
				break;
			case self::WARNING:
				self::warning($message);
				break;
			case self::INFO:
				self::info($message);
				break;
			case self::DEBUG:
				self::debug($message);
				break;
		}
	}

	public static function send(string $message, int $level, string $color){
		if(self::$disableOutput){
			return;
		}
		if($level < self::$logLevel){
			return;
		}
		if(self::$hasPrefix){
			$now = time();
			$class = "Console";
			if(!self::$disableClass){
				$array = @explode("\\", debug_backtrace()[2]['class']);
				$class = end($array);
				if(strlen($class) > 20){
					$class = substr($class, 0, 20);
				}
				$class = $class == "" ? "Console" : $class;
			}
			$message = TextFormat::toANSI(TextFormat::AQUA . "[" . date("G:i:s", $now) . "] " .
				TextFormat::RESET . $color . "<" . $class . "/" . self::PREFIX[$level] . ">" . " " .
				$message . TextFormat::RESET);
		}else{
			$message = TextFormat::toANSI($message . TextFormat::RESET);
		}

		self::$loggerHandler::println($message);
	}

	public static function println(string $message){
		$cleanMessage = TextFormat::clean($message);

		if(!Terminal::hasFormattingCodes()){
			echo $cleanMessage . PHP_EOL;
		}else{
			echo $message . PHP_EOL;
		}

		if(self::$logfile != ""){
			file_put_contents(self::$logfile, $cleanMessage . PHP_EOL, FILE_APPEND);
		}
	}
}
