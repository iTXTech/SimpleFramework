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

namespace iTXTech\SimpleFramework\Console;

use iTXTech\SimpleFramework\Console\Command\ClearCommand;
use iTXTech\SimpleFramework\Console\Command\Command;
use iTXTech\SimpleFramework\Console\Command\HelpCommand;
use iTXTech\SimpleFramework\Console\Command\ModulesCommand;
use iTXTech\SimpleFramework\Console\Command\PackModuleCommand;
use iTXTech\SimpleFramework\Console\Command\PackSFCommand;
use iTXTech\SimpleFramework\Console\Command\StopCommand;
use iTXTech\SimpleFramework\Console\Command\UnpackModuleCommand;
use iTXTech\SimpleFramework\Console\Command\VersionCommand;
use iTXTech\SimpleFramework\Framework;

class CommandProcessor{
	/** @var Command[] */
	private $registeredCommands;

	public function __construct(){
	}

	public function getCommands(){
		return $this->registeredCommands;
	}

	public function registerDefaultCommands(){
		$moduleManager = Framework::getInstance()->getModuleManager();

		if($moduleManager !== null){
			$this->register(new ModulesCommand($moduleManager), "modules");
			$this->register(new PackModuleCommand($moduleManager), "pm");
			$this->register(new UnpackModuleCommand($moduleManager), "um");
		}

		$this->register(new HelpCommand($this), "help");
		$this->register(new VersionCommand(), "version");
		$this->register(new StopCommand(), "stop");
		$this->register(new ClearCommand(), "clear");
		$this->register(new PackSFCommand(), "psf");
	}

	public function register(Command $command, string $name){
		$this->registeredCommands[$name] = $command;
	}

	public function unregister(string $name) : bool{
		if(isset($this->registeredCommands[strtolower($name)])){
			unset($this->registeredCommands[strtolower($name)]);
			return true;
		}
		return false;
	}

	public function dispatchCommand(string $commandLine){
		$args = explode(" ", $commandLine);
		$command = strtolower(array_shift($args));
		if(isset($this->registeredCommands[$command])){
			if(!$this->registeredCommands[$command]->execute($command, $args)){
				Logger::info(TextFormat::GOLD . "Usage: " . TextFormat::WHITE . $this->registeredCommands[$command]->getUsage());
			}
		}else{
			Logger::info(TextFormat::RED . "Command '$command' not found. Type 'help' for help");
		}
	}
}

