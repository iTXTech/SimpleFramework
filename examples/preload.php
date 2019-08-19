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
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

// .\sf p=example\preload.php -h
// and see if -d option has been modified

// .\sf p=phar://modules/A.phar/src/B/Preload.php p=phar://modules/AA.phar/preload.php p=p.php

use iTXTech\SimpleFramework\Console\CmdLineOpt\CmdLineOpt;
use iTXTech\SimpleFramework\Console\Option\CommandLine;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\Options;

CmdLineOpt::reg(ExtraOpts::class);

class ExtraOpts extends CmdLineOpt{
	public static function register(Options $options){
		$options->addOption((new OptionBuilder("d"))->longOpt("test")
			->desc("Test opt")->hasArg()->argName("test-arg")->build());
	}

	public static function process(CommandLine $cmd, Options $options){
	}
}
