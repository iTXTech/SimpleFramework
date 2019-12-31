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
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Console\Option\Parser;

abstract class CmdLineOpt{
	/** @var CmdLineOpt[] */
	private static $registeredOpts = [];

	public abstract static function register(Options $options);

	public abstract static function process(CommandLine $cmd, Options $options);

	public static function reg(string $class){
		if(is_a($class, CmdLineOpt::class, true)){
			self::$registeredOpts[] = $class;
		}
	}

	public static function regAll(){
		self::reg(FrameworkInfo::class);
		self::reg(LoggerSwitches::class);
		self::reg(Properties::class);
		self::reg(CurlOpts::class);
	}

	public static function init(Options $options){
		foreach(self::$registeredOpts as $opt){
			$opt::register($options);
		}
	}

	public static function processAll(array $argv, Options $options){
		$cmd = (new Parser())->parse($options, $argv);
		foreach(self::$registeredOpts as $opt){
			$opt::process($cmd, $options);
		}
	}
}
