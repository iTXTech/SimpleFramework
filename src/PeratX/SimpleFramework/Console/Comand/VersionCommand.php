<?php

/**
 * SimpleFramework
 * The fast, light-weighted, easy-to-extend php framework.
 *
 * Some classes are based on project PocketMine-MP.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PeratX
 */

namespace PeratX\SimpleFramework\Console\Command;

use PeratX\SimpleFramework\Console\Logger;
use PeratX\SimpleFramework\Console\TextFormat;
use PeratX\SimpleFramework\Framework;

class VersionCommand implements Command{
	public function getName() : string{
		return "version";
	}

	public function getUsage() : string{
		return "version (moduleName)";
	}

	public function getDescription() : string{
		return "Gets the version of SimpleFramework or the version of a module.";
	}

	public function execute(string $command, array $args) : bool{
		if(count($args) > 0){
			$module = $args[0];
			$modules = Framework::getInstance()->getModules();
			if(isset($modules[$module])){
				$module = $modules[$module];
				Logger::info(TextFormat::YELLOW . "--------- " . TextFormat::WHITE . "Version: " . $module->getInfo()->getName() . TextFormat::YELLOW . " ---------");
				Logger::info(TextFormat::GOLD . "Version: " . TextFormat::WHITE . $module->getInfo()->getVersion());
				if(($des = $module->getInfo()->getDescription()) != null){
					Logger::info(TextFormat::GOLD . "Description: " . TextFormat::WHITE . $des);
				}
				if(($website = $module->getInfo()->getWebsite()) != null){
					Logger::info(TextFormat::GOLD . "Website: " . TextFormat::WHITE . $website);
				}
			}else{
				Logger::error(TextFormat::RED . "Module " . $module . " is not installed.");
			}
		}else{
			Logger::info(TextFormat::AQUA . "SimpleFramework " . TextFormat::LIGHT_PURPLE . Framework::PROG_VERSION . TextFormat::WHITE . " Implementing API level: " . TextFormat::GREEN . Framework::API_LEVEL);
		}
		return true;
	}
}