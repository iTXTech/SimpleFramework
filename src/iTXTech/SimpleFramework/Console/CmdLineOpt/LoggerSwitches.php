<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2022 iTX Technologies
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

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Option\CommandLine;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Console\Terminal;
use iTXTech\SimpleFramework\Util\Util;

class LoggerSwitches extends CmdLineOpt{
	public static function register(Options $options){
		$options->addOption((new OptionBuilder("a"))->longOpt("ansi")
			->desc("Enable or disable ANSI")->hasArg()->argName("yes|no")->build());
		$options->addOption((new OptionBuilder("e"))->longOpt("disable-logger")
			->desc("Disable Logger output")->build());
		$options->addOption((new OptionBuilder("f"))->longOpt("disable-logger-class")
			->desc("Disable Logger Class detection")->build());
		$options->addOption((new OptionBuilder("g"))->longOpt("without-prefix")
			->desc("Print log without prefix")->build());
	}

	public static function process(CommandLine $cmd, Options $options){
		if($cmd->hasOption("ansi")){
			Terminal::$formattingCodes = Util::getCliOptBool($cmd->getOptionValue("ansi"));
			Terminal::init();
		}
		if($cmd->hasOption("disable-logger")){
			Logger::$disableOutput = true;
		}
		if($cmd->hasOption("disable-logger-class")){
			Logger::$disableClass = true;
		}
		if($cmd->hasOption("without-prefix")){
			Logger::$hasPrefix = false;
		}
	}
}
