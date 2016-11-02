<?php

/**
 * SimpleFramework
 * The fast, light-weighted, easy-to-extend php framework.
 *
 * Some classes are based on project PocketMine-MP.
 * List: ConsoleReader, Terminal, TextFormat, Logger, Util, Config, ClassLoader
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PeratX
 */

namespace sf\console;

use sf\SimpleFramework;
use sf\util\Util;

class CommandProcessor{
	/** @var Command[] */
	private $registeredCommands;

	public function __construct(){
		$this->registerCommands();
	}

	public function getCommands(){
		return $this->registeredCommands;
	}

	public function registerCommands(){
		$this->register(new HelpCommand(), "help");
		$this->register(new VersionCommand(), "version");
		$this->register(new StopCommand(), "stop");
		$this->register(new ModulesCommand(), "modules");
		$this->register(new ClearCommand(), "clear");
	}

	public function register(Command $command, string $name){
		$this->registeredCommands[$name] = $command;
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

//Commands

interface Command{
	public function getName() : string;

	public function getUsage() : string;

	public function getDescription() : string;

	public function execute(string $command, array $args) : bool;
}

class HelpCommand implements Command{
	public function getName() : string{
		return "help";
	}

	public function getUsage() : string{
		return "help (command)";
	}

	public function getDescription() : string{
		return "Gets the help of commands.";
	}

	public function execute(string $command, array $args) : bool{
		$commands = SimpleFramework::getInstance()->getCommandProcessor()->getCommands();
		if(count($args) > 0){
			$command = strtolower($args[0]);
			if(isset($commands[$command])){
				Logger::info(TextFormat::YELLOW . "---------- " . TextFormat::WHITE . "Help: " . $command . TextFormat::YELLOW . " ----------");
				Logger::info(TextFormat::GOLD . "Usage: " . TextFormat::WHITE . $commands[$command]->getUsage());
				Logger::info(TextFormat::GOLD . "Description: " . TextFormat::WHITE . $commands[$command]->getDescription());
			}else{
				Logger::info(TextFormat::RED . "Not found help for $command");
			}
		}else{
			ksort($commands, SORT_NATURAL | SORT_FLAG_CASE);
			Logger::info(TextFormat::YELLOW . "---------- " . TextFormat::WHITE . "Registered Commands: " . count($commands) . TextFormat::YELLOW . " ----------");
			foreach($commands as $command){
				Logger::info(TextFormat::GREEN . $command->getName() . ": " . TextFormat::WHITE . $command->getDescription());
			}
		}
		return true;
	}
}

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
			$modules = SimpleFramework::getInstance()->getModules();
			if(isset($modules[$module])){
				$module = $modules[$module];
				Logger::info(TextFormat::YELLOW . "--------- " . TextFormat::WHITE . "Version: " . $module->getName() . TextFormat::YELLOW . " ---------");
				Logger::info(TextFormat::GOLD . "Version: " . TextFormat::WHITE . $module->getVersion());
				Logger::info(TextFormat::GOLD . "Description: " . TextFormat::WHITE . $module->getDescription());
			}else{
				Logger::error(TextFormat::RED . "Module " . $module . " is not installed.");
			}
		}else{
			Logger::info(TextFormat::AQUA . "SimpleFramework " . TextFormat::LIGHT_PURPLE . SimpleFramework::PROG_VERSION . TextFormat::WHITE . " Implementing API level: " . TextFormat::GREEN . SimpleFramework::API_LEVEL);
		}
		return true;
	}
}

class StopCommand implements Command{
	public function getName() : string{
		return "stop";
	}

	public function getUsage() : string{
		return "stop";
	}

	public function getDescription() : string{
		return "Stop the framework and all the modules.";
	}

	public function execute(string $command, array $args) : bool{
		SimpleFramework::getInstance()->shutdown();
		return true;
	}
}

class ModulesCommand implements Command{
	public function getName() : string{
		return "modules";
	}

	public function getUsage() : string{
		return "modules <list|load|unload|read> <ModuleName> (MainClass)";
	}

	public function getDescription() : string{
		return "The manage command for Modules.";
	}

	public function execute(string $command, array $args) : bool{
		if(count($args) < 1){
			return false;
		}
		$modules = SimpleFramework::getInstance()->getModules();
		switch(strtolower($args[0])){
			case "list":
				$message = "Modules (" . count($modules) . "): ";
				$msg = [];
				foreach($modules as $module){
					$msg[] = ($module->isLoaded() ? TextFormat::GREEN : TextFormat::RED) . $module->getName() . " v" . $module->getVersion();
				}
				$message .= implode(TextFormat::WHITE . ", ", $msg);
				Logger::info($message);
				return true;
			case "load":
				if(count($args) > 1){
					$name = $args[1];
					if(isset($modules[$name])){
						$module = $modules[$name];
						SimpleFramework::getInstance()->loadModule($module);
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
						SimpleFramework::getInstance()->unloadModule($module);
					}else{
						Logger::info(TextFormat::RED . "Module $name is not installed.");
					}
					return true;
				}else{
					return false;
				}
			case "read":
				if(count($args) > 2){
					SimpleFramework::getInstance()->readModule($args[1], $args[2]);
					return true;
				}else{
					return false;
				}
			default:
				return false;
		}
	}
}

class ClearCommand implements Command{
	public function getName() : string{
		return "clear";
	}

	public function getUsage() : string{
		return "clear";
	}

	public function getDescription() : string{
		return "Clears the screen.";
	}

	public function execute(string $command, array $args) : bool{
		echo "\x1bc";
		return true;
	}
}