<?php

/**
 * SimpleFramework
 * The fast, light-weighted, easy-to-extend php framework.
 *
 * Some classes are based on project PocketMine-MP.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PeratX
 */

namespace PeratX\SimpleFramework\Console\Command;

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