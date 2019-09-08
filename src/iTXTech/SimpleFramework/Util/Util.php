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

namespace iTXTech\SimpleFramework\Util;

use iTXTech\SimpleFramework\Util\Curl\Curl;
use iTXTech\SimpleFramework\Util\Curl\Response;

abstract class Util{
	public const USER_AGENT = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36";

	public const OS_WINDOWS = "win";
	public const OS_LINUX = "linux";
	public const OS_MACOS = "mac";
	public const OS_ANDROID = "android";
	public const OS_IOS = "ios";
	public const OS_BSD = "bsd";
	public const OS_OTHER = "other";

	private static $os;

	public static function getURL($page, $timeout = 10, array $extraHeaders = []) : Response{
		return Curl::newInstance()
			->setUrl($page)
			->setOpt(CURLOPT_AUTOREFERER, 1)
			->setOpt(CURLOPT_FOLLOWLOCATION, 1)
			->setOpt(CURLOPT_FORBID_REUSE, 1)
			->setOpt(CURLOPT_FRESH_CONNECT, 1)
			->setTimeout($timeout)
			->setHeader($extraHeaders)
			->setUserAgent(self::USER_AGENT)
			->exec();
	}

	public static function getOS(){
		if(self::$os === null){
			$uname = php_uname("s");
			if(stripos($uname, "Darwin") !== false){
				if(strpos(php_uname("m"), "iP") === 0){
					self::$os = self::OS_IOS;
				}else{
					self::$os = self::OS_MACOS;
				}
			}elseif(stripos($uname, "Win") !== false or $uname === "Msys"){
				self::$os = self::OS_WINDOWS;
			}elseif(stripos($uname, "Linux") !== false){
				if(@file_exists("/system/build.prop")){
					self::$os = self::OS_ANDROID;
				}else{
					self::$os = self::OS_LINUX;
				}
			}elseif(stripos($uname, "BSD") !== false or $uname === "DragonFly"){
				self::$os = self::OS_BSD;
			}else{
				self::$os = self::OS_OTHER;
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

	public static function downloadFile(string $file, string $url) : bool{
		$response = Curl::newInstance()->setUrl($url)
			->setUserAgent(self::USER_AGENT)
			->setTimeout(60)
			->setOpt(CURLOPT_AUTOREFERER, 1)
			->setOpt(CURLOPT_FOLLOWLOCATION, 1)
			->setOpt(CURLOPT_FORBID_REUSE, 1)
			->setOpt(CURLOPT_FRESH_CONNECT, 1)
			->setOpt(CURLOPT_BINARYTRANSFER, 1)
			->setOpt(CURLOPT_BUFFERSIZE, 20971520)
			->exec();

		if($response->isSuccessful()){
			file_put_contents($file, $response->getBody(), FILE_BINARY);
			return true;
		}
		return false;
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

	public static function compareVersion(string $version, string $targetVer) : bool{
		$version = explode(".", $version);
		$targetVersion = explode(".", $targetVer);

		if($version[0] != $targetVersion[0]){
			return true;
		}elseif(count($version) > 1 and ($version[1] > $targetVersion[1])){
			return true;
		}elseif(count($version) > 2 and ($version[1] == $targetVersion[1] and $version[2] > $targetVersion[2])){
			return true;
		}

		return false;
	}

	public static function stripLeadingHyphens(string $str) : string{
		if($str == null){
			return null;
		}
		if(StringUtil::startsWith($str, "--")){
			return substr($str, 2);
		}elseif(StringUtil::startsWith($str, "-")){
			return substr($str, 1);
		}

		return $str;
	}

	public static function stripLeadingAndTrailingQuotes(string $str) : string{
		$length = strlen($str);
		if($length > 1 and StringUtil::startsWith($str, "\"") and
			StringUtil::endsWith($str, "\"") and
			strpos(substr($str, 1, strlen($str) - 1), "\"") === false){
			$str = substr($str, 1, strlen($str) - 1);
		}

		return $str;
	}

	public static function getCliOptBool(string $token) : bool{
		$token = strtolower($token);
		if(in_array($token, ["yes", "y", "true", "t"])){
			return true;
		}
		if(in_array($token, ["no", "n", "false", "f"])){
			return false;
		}
		return false;
	}

	public static function println(string $str){
		echo $str . PHP_EOL;
	}

	/**
	 * @param string $ext
	 *
	 * @return string
	 * @throws \ReflectionException
	 */
	public static function generateExtensionInfo(string $ext) : string{
		$info = $ext . " => ";
		if(extension_loaded($ext)){
			$extension = new \ReflectionExtension($ext);
			$info .= $extension->getVersion();
		}else{
			$info .= "not installed";
		}

		return $info;
	}

	/**
	 * @param string $dir Should be ended with a DIRECTORY_SEPARATOR
	 *
	 * @return string|null
	 */
	public static function getLatestGitCommitId(string $dir) : ?string{
		$dir .= ".git" . DIRECTORY_SEPARATOR;
		if(file_exists($dir)){
			return trim(file_get_contents($dir .
				str_replace("/", DIRECTORY_SEPARATOR,
					explode(": ", trim(file_get_contents($dir . "HEAD")))[1])));
		}
		return null;
	}
}
