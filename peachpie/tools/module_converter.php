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

/*
 * This script converts a standard SimpleFramework module
 * into SimpleFramework for PeachPie compatible module.
 *
 * Note that this script will not process ANY php code.
 * It simply filters sources files and converts resource files
 * into php files which will be processed by PeachPie compiler.
 *
 * The module must be developed to be compatible with both
 * standard SimpleFramework and SimpleFramework for PeachPie.
 *
 * See iTXTech FlashDetector for example.
 * https://github.com/iTXTech/FlashDetector
 *
 * For more information about SimpleFramework for PeachPie,
 * see https://github.com/iTXTech/SimpleFramework/tree/peachpie
 *
 * Example usage:
 * php .\module_converter.php -i D:\development\FlashDetector\FlashDetector
 * -o D:\development\FlashDetector\PeachPie\FlashDetector\ -e Online,vendor,composer,stub.php,Packer
 */

require_once $_ENV["SF_HOME"] . DIRECTORY_SEPARATOR . "sfloader.php";

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Option\Exception\ParseException;
use iTXTech\SimpleFramework\Console\Option\HelpFormatter;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Console\Option\Parser;
use iTXTech\SimpleFramework\Framework;
use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Util\Util;

Initializer::initTerminal(true);
Framework::registerExceptionHandler();

$options = new Options();
$options->addOption((new OptionBuilder("i"))->longOpt("input")->argName("dir")
	->hasArg()->required()->desc("Module input dir")->build());
$options->addOption((new OptionBuilder("o"))->longOpt("output")->argName("dir")
	->hasArg()->required()->desc("Module output dir")->build());
$options->addOption((new OptionBuilder("e"))->longOpt("exclude")->argName("files")
	->hasArg()->desc("Excluded files, example: .json,example,.txt")->build());
$options->addOption((new OptionBuilder("r"))->longOpt("resource")->argName("types")
	->hasArg()->desc("Resource file types, example: json,txt,yml")->build());

try{
	$cmd = (new Parser())->parse($options, $argv);
	Util::moduleToPeachPieModule($cmd->getOptionValue("i"), $cmd->getOptionValue("o"),
		explode(",", $cmd->getOptionValue("e", "")),
		explode(",", $cmd->getOptionValue("r", "json")));
	Logger::info("All files have been processed.");
}catch(ParseException $e){
	echo (new HelpFormatter())->generateHelp("module_converter", $options, true);
}
