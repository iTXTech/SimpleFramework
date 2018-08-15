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
use iTXTech\SimpleFramework\Framework;

class PackSFCommand implements Command{
	public function getName() : string{
		return "psf";
	}

	public function getUsage() : string{
		return "psf  (no-gz) (no-echo)";
	}

	public function getDescription() : string{
		return "Pack the framework into Phar archive.";
	}

	public function execute(string $command, array $args) : bool{
		$outputDir = Framework::getInstance()->getModuleManager()->getModuleDataPath() . "module" . DIRECTORY_SEPARATOR;
		@mkdir($outputDir);
		$framework = Framework::getInstance();
		$pharPath = $outputDir . $framework->getName() . "_" . $framework->getVersion() . ".phar";
		if(file_exists($pharPath)){
			Logger::info("Phar file already exists, overwriting...");
			@\Phar::unlinkArchive($pharPath);
		}
		$phar = new \Phar($pharPath);
		$phar->setMetadata([
			"name" => $framework->getName(),
			"version" => $framework->getVersion(),
			"api" => $framework->getAPILevel(),
			"creationDate" => time()
		]);
		$phar->setStub('<?php define("iTXTech\\\\SimpleFramework\\\\PATH", "phar://". __FILE__ ."/"); require_once("phar://". __FILE__ ."/src/iTXTech/SimpleFramework/SimpleFramework.php");  __HALT_COMPILER();');
		$phar->setSignatureAlgorithm(\Phar::SHA1);
		$phar->startBuffering();

		$filePath = substr(\iTXTech\SimpleFramework\PATH, 0, 7) === "phar://" ? \iTXTech\SimpleFramework\PATH : realpath(\iTXTech\SimpleFramework\PATH) . "/";
		$filePath = rtrim(str_replace("\\", "/", $filePath), "/") . "/";
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filePath . "src")) as $file){
			$path = ltrim(str_replace(["\\", $filePath], ["/", ""], $file), "/");
			if($path{0} === "." or strpos($path, "/.") !== false or substr($path, 0, 4) !== "src/"){
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

		Logger::info($framework->getName() . " " . $framework->getVersion() . " Phar archive has been created in " . $pharPath);

		return true;
	}
}