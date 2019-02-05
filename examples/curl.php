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

require_once "../autoload.php";

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Framework;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Util\Curl\Curl;
use iTXTech\SimpleFramework\Util\Curl\Preprocessor;

Logger::$logLevel = Logger::DEBUG;
Initializer::initTerminal(true);

class CustomizedCurl extends Curl{
	/** @var Preprocessor */
	public static $processor;

	public static function init(){
		self::$processor = new class implements Preprocessor{
			public function process(Curl $curl){
				$u = parse_url($curl->getUrl());
				Logger::debug("Parsed URL: " . json_encode($u));
			}
		};
	}

	public function __construct(){
		parent::__construct()->setPreprocessor(self::$processor);
	}
}

CustomizedCurl::init();
$res = Curl::setCurlClass(CustomizedCurl::class);
Logger::info("CustomizedCurl init result: " . ($res ? "true" : "false"));

$curl = Curl::newInstance();
$resp = $curl->setUrl("https://github.com")
	->setHeader("Expect")
	->setUserAgent(Framework::PROG_NAME . " " . Framework::PROG_VERSION)
	->exec();

if($resp->isSuccessful()){
	Logger::info("Code: " . $resp->getHttpCode());
	Logger::info("Got " . count($resp->getCookies()) . " cookies");
	Logger::info("Got " . count($resp->getHeaders()) . " headers");
	Logger::info("Body len: " . strlen($resp->getBody()));
}else{
	Logger::error("Cannot reach target, errno: " . $resp->getErrno());
}
