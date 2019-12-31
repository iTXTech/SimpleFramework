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

namespace iTXTech\SimpleFramework\Console\CmdLineOpt;

use iTXTech\SimpleFramework\Console\Option\CommandLine;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Framework;
use iTXTech\SimpleFramework\Util\StringUtil;

class Properties extends CmdLineOpt{
	public static function register(Options $options){
		$options->addOption((new OptionBuilder("b"))->longOpt("config")
			->desc("Overwrite specified config property")->hasArg()->argName("prop")->build());

		$options->addOption((new OptionBuilder("l"))->longOpt("data-path")
			->desc("Specify data path")->hasArg()->argName("path")->build());
		$options->addOption((new OptionBuilder("m"))->longOpt("module-path")
			->desc("Specify module path")->hasArg()->argName("path")->build());
		$options->addOption((new OptionBuilder("n"))->longOpt("module-data-path")
			->desc("Specify module data path")->hasArg()->argName("path")->build());
		$options->addOption((new OptionBuilder("o"))->longOpt("config-path")
			->desc("Specify config file")->hasArg()->argName("path")->build());


		$options->addOption((new OptionBuilder("r"))->longOpt("load-module")->hasArg()
			->desc("Load the specified module")->argName("path")->build());
		$options->addOption((new OptionBuilder("s"))->longOpt("run-command")->hasArg()
			->desc("Execute the specified command")->argName("command")->build());
	}

	public static function process(CommandLine $cmd, Options $options){
		$prop = Framework::getInstance()->getProperties();
		if($cmd->hasOption("data-path")){
			$prop->dataPath = $cmd->getOptionValue("data-path");
			$prop->generatePath();
		}
		if($cmd->hasOption("config-path")){
			$prop->configPath = $cmd->getOptionValue("config-path");
		}
		if($cmd->hasOption("module-path")){
			$prop->modulePath = $cmd->getOptionValue("module-path");
		}
		if($cmd->hasOption("module-data-path")){
			$prop->moduleDataPath = $cmd->getOptionValue("module-data-path");
		}
		if($cmd->hasOption("load-module")){
			foreach($cmd->getOptionValues("load-module") as $value){
				$prop->additionalModules[] = $value;
			}
		}
		if($cmd->hasOption("run-command")){
			foreach($cmd->getOptionValues("run-command") as $value){
				$prop->commands[] = $value;
			}
		}
		if($cmd->hasOption("config")){
			foreach($cmd->getOptionValues("config") as $value){
				list($k, $v) = explode("=", $value, 2);
				if(strtolower($v) == "false"){
					$v = false;
				}elseif(strtolower($v) == "true"){
					$v = true;
				}
				if(StringUtil::contains($k, ".")){
					list($k1, $k2) = explode(".", $k);
					$prop->config[$k1][$k2] = $v;
				}else{
					$prop->config[$k] = $v;
				}
			}
		}
	}
}
