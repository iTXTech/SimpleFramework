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
use iTXTech\SimpleFramework\Util\Curl\Curl;
use iTXTech\SimpleFramework\Util\Curl\UrlResolver;

Logger::$logLevel = Logger::DEBUG;
Initializer::initTerminal(true);

class CustomizedCurl extends Curl{
	public function __construct(){
		parent::__construct()->setResolver(new class implements UrlResolver{
			public function resolve(string $url) : string{
				$u = parse_url($url);
				Logger::debug("Parsed URL: " . json_encode($u));
				return $url;
			}
		});
	}
}

$res = Curl::setCurlClass(CustomizedCurl::class);
Logger::info("CustomizedCurl init result: " . ($res ? "true" : "false"));

$curl = Curl::newInstance();
$resp = $curl->setUrl("https://github.com")
	->setUserAgent(Framework::PROG_NAME . " " . Framework::PROG_VERSION)
	->exec();

if($resp->isSuccessfully()){
	Logger::info("Code: " . $resp->getHttpCode());
	Logger::info("Got " . count($resp->getCookies()) . " cookies");
	Logger::info("Got " . count($resp->getHeaders()) . " headers");
	Logger::info("Body len: " . strlen($resp->getBody()));
}else{
	Logger::error("Cannot reach target");
}
