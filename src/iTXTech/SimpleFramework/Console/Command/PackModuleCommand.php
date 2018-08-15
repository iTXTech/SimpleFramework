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

class PackModuleCommand implements Command{
	public function getName() : string{
		return "pm";
	}

	public function getUsage() : string{
		return "pm <Module Name> (no-gz) (no-echo)";
	}

	public function getDescription() : string{
		return "Pack a source module into Phar archive.";
	}

	public function execute(string $command, array $args) : bool{
		$moduleName = trim(str_replace(["no-gz", "no-echo"], "", implode(" ", $args)));

		if($moduleName === "" or !(($module = Framework::getInstance()->getModuleManager()->getModule($moduleName)) instanceof Module)){
			Logger::info(TextFormat::RED . "Invalid module name, check the name case.");
			return true;
		}
		$info = $module->getInfo();

		if(!($info->getLoadMethod() == ModuleInfo::LOAD_METHOD_SOURCE)){
			Logger::info(TextFormat::RED . "Module " . $info->getName() . " is not in folder structure.");
			return true;
		}

		$outputDir = Framework::getInstance()->getModuleManager()->getModuleDataPath() . "module" . DIRECTORY_SEPARATOR;
		@mkdir($outputDir);
		$pharPath = $outputDir . $info->getName() . "_v" . $info->getVersion() . ".phar";
		if(file_exists($pharPath)){
			Logger::info("Phar module already exists, overwriting...");
			@\Phar::unlinkArchive($pharPath);
		}
		$phar = new \Phar($pharPath);
		$phar->setMetadata([
			"name" => $info->getName(),
			"version" => $info->getVersion(),
			"main" => $info->getMain(),
			"api" => $info->getAPILevel(),
			"description" => $info->getDescription(),
			"authors" => $info->getAuthors(),
			"creationDate" => time()
		]);
		$phar->setStub('<?php echo "' . Framework::PROG_NAME . ' module ' . $info->getName() . ' v' . $info->getVersion() . '\nThis file has been generated using PackModule Command at ' . date("r") . '\n----------------\n";if(extension_loaded("phar")){$phar = new \Phar(__FILE__);foreach($phar->getMetadata() as $key => $value){echo ucfirst($key).": ".(is_array($value) ? implode(", ", $value):$value)."\n";}} __HALT_COMPILER();');
		$phar->setSignatureAlgorithm(\Phar::SHA1);
		$filePath = rtrim(str_replace("\\", "/", $module->getFile()), "/") . "/";
		$phar->startBuffering();
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filePath)) as $file){
			$path = ltrim(str_replace(["\\", $filePath], ["/", ""], $file), "/");
			if($path{0} === "." or strpos($path, "/.") !== false){
				continue;
			}
			$phar->addFile($file, $path);
			if(!in_array("no-echo", $args)){
				Logger::info("Adding $path");
			}
		}

		foreach($phar as $file => $finfo){
			/** @var \PharFileInfo $finfo */
			if($finfo->getSize() > (1024 * 512)){
				$finfo->compress(\Phar::GZ);
			}
		}
		if(!in_array("no-gz", $args)){
			$phar->compressFiles(\Phar::GZ);
		}
		$phar->stopBuffering();
		Logger::info("Phar module " . $info->getName() . " v" . $info->getVersion() . " has been created in " . $pharPath);
		return true;
	}
}