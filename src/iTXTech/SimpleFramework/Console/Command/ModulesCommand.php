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

namespace iTXTech\SimpleFramework\Console\Command;

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleFramework\Module\ModuleManager;
use iTXTech\SimpleFramework\Util\Timer;

class ModulesCommand implements Command{
	/** @var ModuleManager */
	private $manager;

	public function __construct(ModuleManager $manager){
		$this->manager = $manager;
	}

	public function getName() : string{
		return "modules";
	}

	public function getUsage() : string{
		return "modules <list|load|unload|read|hotpatch> <ModuleName or File/Folder Name>";
	}

	public function getDescription() : string{
		return "The manage command for Modules.";
	}

	public function execute(string $command, array $args) : bool{
		if(count($args) < 1){
			return false;
		}
		$modules = $this->manager->getModules();
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
						$this->manager->loadModule($module);
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
						$this->manager->unloadModule($module);
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
					if(!file_exists($this->manager->getModulePath() . $file)){
						Logger::info(TextFormat::RED . "File not found.");
						return true;
					}
					$this->manager->tryLoadModule($this->manager->getModulePath() . $file);
					return true;
				}else{
					return false;
				}
			case "hotpatch":
				if(count($args) > 1){
					$name = $args[1];
					if(isset($modules[$name])){
						$module = $modules[$name];
						$timer = new Timer();
						$timer->start();
						$module->doHotPatch();
						Logger::info("HotPatch for $name took " . $timer->stop() . " s");
					}else{
						Logger::info(TextFormat::RED . "Module $name is not installed.");
					}
					return true;
				}else{
					return false;
				}
			default:
				return false;
		}
	}
}
