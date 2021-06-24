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

namespace iTXTech\SimpleFramework\Console\Command;

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleFramework\Module\Module;
use iTXTech\SimpleFramework\Module\ModuleManager;
use iTXTech\SimpleFramework\Module\Packer;

class PackModuleCommand implements Command{
	/** @var ModuleManager */
	private $manager;

	public function __construct(ModuleManager $manager){
		$this->manager = $manager;
	}

	public function getName() : string{
		return "pm";
	}

	public function getUsage() : string{
		return "pm <Module Name> (no-gz) (no-echo) (no-git)";
	}

	public function getDescription() : string{
		return "Pack a source module into Phar archive.";
	}

	public function execute(string $command, array $args) : bool{
		$moduleName = trim(str_replace(["no-gz", "no-echo", "no-git"], "", implode(" ", $args)));

		if($moduleName === "" or !(($module = $this->manager->getModule($moduleName)) instanceof Module)){
			Logger::info(TextFormat::RED . "Invalid module name, check the name case.");
			return true;
		}
		$module->pack(Packer::VARIANT_TYPICAL, $this->manager->getModuleDataPath() . "module" . DIRECTORY_SEPARATOR,
			null, !in_array("no-git", $args), !in_array("no-gz", $args), !in_array("no-echo", $args));
		if($module->getInfo()->composer()){
			$module->pack(Packer::VARIANT_TYPICAL, $this->manager->getModuleDataPath() . "module" . DIRECTORY_SEPARATOR,
				$module->getName() . "_v" . $module->getInfo()->getVersion() . "_composer.phar",
				!in_array("no-git", $args), !in_array("no-gz", $args), !in_array("no-echo", $args));
		}
		return true;
	}
}
