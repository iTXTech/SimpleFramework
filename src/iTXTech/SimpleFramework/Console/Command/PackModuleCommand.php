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

namespace iTXTech\SimpleFramework\Console\Command;

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleFramework\Module\Module;
use iTXTech\SimpleFramework\Module\ModuleManager;

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
		$module->pack($this->manager->getModuleDataPath() . "module" . DIRECTORY_SEPARATOR, null,
			!in_array("no-git", $args), !in_array("no-gz", $args), !in_array("no-echo", $args));
		return true;
	}
}
