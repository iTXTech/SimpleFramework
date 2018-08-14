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

use iTXTech\SimpleFramework\Framework;

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
		Framework::getInstance()->shutdown();
		return true;
	}
}