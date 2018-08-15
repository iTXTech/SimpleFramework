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

class ModulesCommand implements Command{
	public function getName() : string{
		return "modules";
	}

	public function getUsage() : string{
		return "modules <list|load|unload|read> <ModuleName or File/Folder Name>";
	}

	public function getDescription() : string{
		return "The manage command for Modules.";
	}

	public function execute(string $command, array $args) : bool{
		if(count($args) < 1){
			return false;
		}
		$modules = Framework::getInstance()->getModuleManager()->getModules();
		switch(strtolower($args[0])){
			case "list":
				$message = "Modules (" . count($modules) . "): ";
				$msg = [];
				foreach($modules as $module){
					$msg[] = ($module->isLoaded() ? TextFormat::GREEN : TextFormat::RED) . $module->getInfo()->getName() . " v" . $module->getInfo()->getVersion();
				}
				$message .= implode(TextFormat::WHITE . ", ", $msg);
				Logger::info($message);
				return true;
			case "load":
				if(count($args) > 1){
					$name = $args[1];
					if(isset($modules[$name])){
						$module = $modules[$name];
						Framework::getInstance()->getModuleManager()->loadModule($module);
					}else{
						Logger::info(TextFormat::RED . "Module $name is not installed.");
					}
					return true;
				}else{
					return false;
				}
			case "unload":
				if(count($args) > 1){
					$name = $args[1];
					if(isset($modules[$name])){
						$module = $modules[$name];
						Framework::getInstance()->getModuleManager()->unloadModule($module);
					}else{
						Logger::info(TextFormat::RED . "Module $name is not installed.");
					}
					return true;
				}else{
					return false;
				}
			case "read":
				if(count($args) > 1){
					$file = $args[1];
					if(!file_exists(Framework::getInstance()->getModuleManager()->getModulePath() . $file)){
						Logger::info(TextFormat::RED . "File not found.");
						return true;
					}
					Framework::getInstance()->getModuleManager()->tryLoadModule(Framework::getInstance()->getModuleManager()->getModulePath() . $file);
					return true;
				}else{
					return false;
				}
			default:
				return false;
		}
	}
}