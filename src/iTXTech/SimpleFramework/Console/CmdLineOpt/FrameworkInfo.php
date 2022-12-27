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

use iTXTech\SimpleFramework\Console\Option\CommandLine;
use iTXTech\SimpleFramework\Console\Option\HelpFormatter;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Framework;
use iTXTech\SimpleFramework\Util\Util;

class FrameworkInfo extends CmdLineOpt{
	public static function register(Options $options){
		$options->addOption((new OptionBuilder("h"))->longOpt("help")
			->desc("Display this help message")->build());
		$options->addOption((new OptionBuilder("v"))->longOpt("version")
			->desc("Display version of SimpleFramework")->build());
	}

	public static function process(CommandLine $cmd, Options $options){
		if($cmd->hasOption("help")){
			$t = (new HelpFormatter())->generateHelp("sf", $options);
			echo $t;
			exit(0);
		}
		if($cmd->hasOption("version")){
			$info = Framework::PROG_NAME . " " . Framework::PROG_VERSION .
				" \"" . Framework::CODENAME . "\" (API " . Framework::API_LEVEL . ")";
			if(\iTXTech\SimpleFramework\SINGLE_THREAD){
				$info .= " [Single Thread]";
			}
			if(($phar = \Phar::running(true)) !== ""){
				$phar = new \Phar($phar);
				$built = date("r", $phar->getMetadata()["creationDate"]) . " (Phar)";
				$git = $phar->getMetadata()["revision"];
			}else{
				$built = date("r") . " (Source)";
				$git = Util::getLatestGitCommitId(\iTXTech\SimpleFramework\PATH) ?? "Unknown";
			}

			Util::println($info);
			Util::println("Built: " . $built);
			Util::println("Revision: " . $git);
			Util::println("Copyright (C) 2016-2022 iTX Technologies");
			Util::println("https://github.com/iTXTech/SimpleFramework");
			Util::println(str_repeat("-", 50));
			Util::println("OS => " . PHP_OS_FAMILY . " " . php_uname("r"));
			Util::println("PHP => " . PHP_VERSION);
			foreach(["curl", "ffi", "pthreads", "runkit7", "swoole", "yaml"] as $ext){
				Util::println(Util::generateExtensionInfo($ext));
			}
			exit(0);
		}
	}
}
