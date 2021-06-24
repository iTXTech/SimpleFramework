<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2021 iTX Technologies
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
			Logger::info(TextFormat::AQUA . Framework::PROG_NAME . " " . TextFormat::LIGHT_PURPLE . Framework::PROG_VERSION . " " . TextFormat::GREEN . "[PHP " . PHP_VERSION . "]" . TextFormat::WHITE . " Implementing API " . TextFormat::GREEN . Framework::API_LEVEL . " " . TextFormat::GOLD . "[" . Framework::CODENAME . "]");
		}
		return true;
	}
}
