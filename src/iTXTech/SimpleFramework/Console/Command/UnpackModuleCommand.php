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

class UnpackModuleCommand implements Command{
	/** @var ModuleManager */
	private $manager;

	public function __construct(ModuleManager $manager){
		$this->manager = $manager;
	}
	
	public function getName() : string{
		return "um";
	}

	public function getUsage() : string{
		return "um <Module Name>";
	}

	public function getDescription() : string{
		return "Unpack a module into source code.";
	}

	public function execute(string $command, array $args) : bool{
		$moduleName = trim(implode(" ", $args));
		if($moduleName === "" or !(($module = $this->manager->getModule($moduleName)) instanceof Module)){
			Logger::info(TextFormat::RED . "Invalid module name, check the name case.");
			return true;
		}

		$module->unpack($this->manager->getModuleDataPath() . "module" . DIRECTORY_SEPARATOR, null, true);
		return true;
	}
}
