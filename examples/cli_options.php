<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2021 iTX Technologies
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

require_once "../autoload.php";

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Option\HelpFormatter;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\OptionGroup;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Console\Option\Parser;
use iTXTech\SimpleFramework\Initializer;

Initializer::initTerminal(true);

$options = new Options();

try{
	$options->addOption((new OptionBuilder("b"))->desc("This is a long opt")
		->longOpt("long-opt")->required()->build());
	$options->addOption((new OptionBuilder("a"))->desc("You need to fill the arg")
		->required()->hasArg()->argName("something")->build());

	$group = new OptionGroup();
	$group->addOption((new OptionBuilder("one"))->desc("This is the first opt in OG")
		->longOpt("first-opt")->build());
	$group->addOption((new OptionBuilder("two"))->desc("This is the second opt in OG")
		->longOpt("second-opt")->build());
	$group->addOption((new OptionBuilder("three"))->desc("This is the third opt in OG")
		->longOpt("third-opt")->build());
	$group->setRequired(false);
	$options->addOptionGroup($group);

	$cmd = (new Parser())->parse($options, $argv);
	Logger::info("Got a: " . $cmd->getOptionValue("a"));
}catch(Throwable $e){
	//print help
	$help = (new HelpFormatter())->generateHelp("cliopts", $options, true);
	echo $help;
}
