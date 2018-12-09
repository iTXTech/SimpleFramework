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

namespace iTXTech\SimpleFramework\Util;

use iTXTech\SimpleFramework\Console\CommandProcessor;
use iTXTech\SimpleFramework\Module\ModuleManager;

class FrameworkProperties{
	public $dataPath;
	public $modulePath;
	public $moduleDataPath;
	public $configPath;

	public $additionalModules = [];
	public $commands = [];
	public $config = [];

	public function generatePath(){
		if(StringUtil::endsWith($this->dataPath, DIRECTORY_SEPARATOR)){
			$this->dataPath .= DIRECTORY_SEPARATOR;
		}
		$this->modulePath = $this->dataPath . "modules" . DIRECTORY_SEPARATOR;
		$this->moduleDataPath = $this->dataPath . "data" . DIRECTORY_SEPARATOR;
		$this->configPath = $this->dataPath . "config.json";
	}

	public function mkdirDirs(){
		self::mkdirDir($this->dataPath);
		self::mkdirDir($this->modulePath);
		self::mkdirDir($this->moduleDataPath);
	}

	public function mergeConfig(Config $config){
		$conf = $config->getAll();
		foreach($this->config as $k => $v){
			if(is_array($v)){
				foreach($v as $k1 => $v1){
					$conf[$k][$k1] = $v1;
				}
			}else{
				$conf[$k] = $v;
			}
		}
		$config->setAll($conf);
	}

	public function loadModules(ModuleManager $manager){
		foreach($this->additionalModules as $module){
			$manager->tryLoadModule($module);
		}
	}

	public function runCommands(CommandProcessor $processor){
		foreach($this->commands as $command){
			$processor->dispatchCommand($command);
		}
	}

	private static function mkdirDir(string $dir){
		if($dir !== "" and !file_exists($dir)){
			@mkdir($dir);
		}
	}
}
