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
use iTXTech\SimpleFramework\Util\Curl\InterfaceSelector;
use iTXTech\SimpleFramework\Util\StringUtil;

class CurlOpts extends CmdLineOpt{
	public static function register(Options $options){
		$options->addOption((new OptionBuilder("c"))->longOpt("curl-proxy")
			->desc("Set global proxy for CURL")->hasArg()->argName("prop")->build());
		$options->addOption((new OptionBuilder("d"))->longOpt("curl-interface")
			->desc("Set global interface(s) for CURL")->hasArgs()->argName("interface")->build());
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
		//example --curl-interface=192.168.2.10@2 --curl-interface=192.168.1.10@8
		if($cmd->hasOption("curl-interface")){
			foreach($cmd->getOptionValues("curl-interface") as $if){
				if(StringUtil::contains($if, "@")){
					list($i, $c) = explode("@", $if);
				}else{
					$i = $if;
					$c = 1;
				}
				InterfaceSelector::registerInterface($i, $c);
			}
		}
	}
}
