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
		$commands = Framework::getInstance()->getCommandProcessor()->getCommands();
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