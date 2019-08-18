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

namespace iTXTech\SimpleFramework\Console\CmdLineOpt;

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Option\CommandLine;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Util\Curl\Curl;

class CurlProxy extends CmdLineOpt{
	public static function register(Options $options){
		$options->addOption((new OptionBuilder("c"))->longOpt("curl-proxy")
			->desc("Set global proxy for SimpleFramework CURL")->hasArg()->argName("prop")->build());
	}

	public static function process(CommandLine $cmd, Options $options){
		//example --curl-proxy=socks://username:password@hostname:port
		if($cmd->hasOption("curl-proxy")){
			if(parse_url($proxy = $cmd->getOptionValue("curl-proxy"))){
				Curl::$GLOBAL_PROXY = $proxy;
			}else{
				Logger::error("Invalid proxy for curl");
			}
		}
	}
}
