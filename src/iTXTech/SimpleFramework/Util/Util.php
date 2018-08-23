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

namespace iTXTech\SimpleFramework\Util;

class Util{
	const USER_AGENT = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36";

	private static $os;

	public static function getURL($page, $timeout = 10, array $extraHeaders = []){
		$curl = new Curl();
		return $curl->setUrl($page)
			->setOpt(CURLOPT_AUTOREFERER, 1)
			->setOpt(CURLOPT_FOLLOWLOCATION, 1)
			->setOpt(CURLOPT_FORBID_REUSE, 1)
			->setOpt(CURLOPT_FRESH_CONNECT, 1)
			->setTimeout($timeout)
			->setHeader($extraHeaders)
			->returnHeader(false)
			->setUA(self::USER_AGENT)
			->exec();
	}

	public static function getOS(){
		if(self::$os === null){
			$uname = php_uname("s");
			if(stripos($uname, "Darwin") !== false){
				if(strpos(php_uname("m"), "iP") === 0){
					self::$os = "ios";
				}else{
					self::$os = "mac";
				}
			}elseif(stripos($uname, "Win") !== false or $uname === "Msys"){
				self::$os = "win";
			}elseif(stripos($uname, "Linux") !== false){
				if(@file_exists("/system/build.prop")){
					self::$os = "android";
				}else{
					self::$os = "linux";
				}
			}elseif(stripos($uname, "BSD") !== false or $uname === "DragonFly"){
				self::$os = "bsd";
			}else{
				self::$os = "other";
			}
		}

		return self::$os;
	}

	public static function printable($str){
		if(!is_string($str)){
			return gettype($str);
		}

		return preg_replace('#([^\x20-\x7E])#', '.', $str);
	}

	public static function downloadFile(string $file, string $url){
		$curl = new Curl();
		$ret = $curl->setUrl($url)
			->setUA(self::USER_AGENT)
			->setTimeout(60)
			->setOpt(CURLOPT_AUTOREFERER, 1)
			->setOpt(CURLOPT_FOLLOWLOCATION, 1)
			->setOpt(CURLOPT_FORBID_REUSE, 1)
			->setOpt(CURLOPT_FRESH_CONNECT, 1)
			->setOpt(CURLOPT_BINARYTRANSFER, 1)
			->setOpt(CURLOPT_BUFFERSIZE, 20971520)
			->returnHeader(false)
			->exec();

		if($ret != false){
			file_put_contents($file, $ret, FILE_BINARY);
		}
	}

	public static function getTrace($start = 0, $trace = null){
		if($trace === null){
			if(function_exists("xdebug_get_function_stack")){
				$trace = array_reverse(xdebug_get_function_stack());
			}else{
				$e = new \Exception();
				$trace = $e->getTrace();
			}
		}

		$messages = [];
		$j = 0;
		for($i = (int) $start; isset($trace[$i]); ++$i, ++$j){
			$params = "";
			if(isset($trace[$i]["args"]) or isset($trace[$i]["params"])){
				if(isset($trace[$i]["args"])){
					$args = $trace[$i]["args"];
				}else{
					$args = $trace[$i]["params"];
				}
				foreach($args as $name => $value){
					$params .= (is_object($value) ? get_class($value) . " object" : gettype($value) . " " . (is_array($value) ? "Array()" : Util::printable(@strval($value)))) . ", ";
				}
			}
			$messages[] = "#$j " . (isset($trace[$i]["file"]) ? $trace[$i]["file"] : "") . "(" . (isset($trace[$i]["line"]) ? $trace[$i]["line"] : "") . "): " . (isset($trace[$i]["class"]) ? $trace[$i]["class"] . (($trace[$i]["type"] === "dynamic" or $trace[$i]["type"] === "->") ? "->" : "::") : "") . $trace[$i]["function"] . "(" . Util::printable(substr($params, 0, -2)) . ")";
		}

		return $messages;
	}

	public static function formatTime($time){
		$seconds = floor($time % 60);
		$minutes = null;
		$hours = null;
		$days = null;

		if($time >= 60){
			$minutes = floor(($time % 3600) / 60);
			if($time >= 3600){
				$hours = floor(($time % (3600 * 24)) / 3600);
				if($time >= 3600 * 24){
					$days = floor($time / (3600 * 24));
				}
			}
		}

		$readable = ($minutes !== null ?
				($hours !== null ?
					($days !== null ?
						"$days d "
						: "") . "$hours h "
					: "") . "$minutes m "
				: "") . "$seconds s";
		return $readable;
	}
}