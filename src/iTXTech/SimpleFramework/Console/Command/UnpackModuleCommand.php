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

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleFramework\Framework;
use iTXTech\SimpleFramework\Module\Module;
use iTXTech\SimpleFramework\Module\ModuleInfo;

class UnpackModuleCommand implements Command{
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
		if($moduleName === "" or !(($module = Framework::getInstance()->getModuleManager()->getModule($moduleName)) instanceof Module)){
			Logger::info(TextFormat::RED . "Invalid module name, check the name case.");
			return true;
		}
		$info = $module->getInfo();

		if(!($info->getLoadMethod() == ModuleInfo::LOAD_METHOD_PACKAGE)){
			Logger::info(TextFormat::RED . "Module " . $info->getName() . " is not in Phar structure.");
			return true;
		}

		$outputDir = Framework::getInstance()->getModuleManager()->getModuleDataPath() . "module" . DIRECTORY_SEPARATOR;
		$folderPath = $outputDir . $info->getName() . "_v" . $info->getVersion() . DIRECTORY_SEPARATOR;
		if(file_exists($folderPath)){
			Logger::info("Module files already exist, overwriting...");
		}else{
			@mkdir($folderPath);
		}

		$pharPath = str_replace("\\", "/", rtrim($module->getFile(), "\\/"));

		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pharPath)) as $fInfo){
			$path = $fInfo->getPathname();
			@mkdir(dirname($folderPath . str_replace($pharPath, "", $path)), 0755, true);
			file_put_contents($folderPath . str_replace($pharPath, "", $path), file_get_contents($path));
		}
		Logger::info("Module " . $info->getName() . " v" . $info->getVersion() . " has been unpacked into " . $folderPath);
		return true;
	}
}