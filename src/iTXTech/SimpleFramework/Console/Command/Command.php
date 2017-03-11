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
 * @author iTXTech
 * @link https://itxtech.org
 */

namespace iTXTech\SimpleFramework\Console\Command;

interface Command{
	public function getName(): string;

	public function getUsage(): string;

	public function getDescription(): string;

	public function execute(string $command, array $args): bool;
}