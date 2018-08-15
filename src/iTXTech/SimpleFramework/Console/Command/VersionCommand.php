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

namespace iTXTech\SimpleFramework\Console\Command;

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleFramework\Framework;

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
			$modules = Framework::getInstance()->getModuleManager()->getModules();
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
			Logger::info(TextFormat::AQUA . Framework::PROG_NAME . " " . TextFormat::LIGHT_PURPLE . Framework::PROG_VERSION . " " . TextFormat::GREEN . "[PHP " . PHP_VERSION . "]" . TextFormat::WHITE . " Implementing API level: " . TextFormat::GREEN . Framework::API_LEVEL . " " . TextFormat::GOLD . "[" . Framework::CODENAME . "]");
		}
		return true;
	}
}