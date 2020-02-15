<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2020 iTX Technologies
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

namespace iTXTech\SimpleFramework\Module;

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;

class ModuleManager{
	/** @var Module[] */
	public $modules = [];

	/** @var \ClassLoader */
	private $classLoader;

	private $modulePath;
	private $moduleDataPath;

	public function __construct(\ClassLoader $loader, string $modulePath, string $moduleDataPath){
		$this->classLoader = $loader;
		$this->modulePath = $modulePath;
		$this->moduleDataPath = $moduleDataPath;
	}

	public function setModulePath(string $modulePath) : void{
		$this->modulePath = $modulePath;
	}

	public function setModuleDataPath(string $moduleDataPath) : void{
		$this->moduleDataPath = $moduleDataPath;
	}

	public function getModulePath() : string{
		return $this->modulePath;
	}

	public function getModuleDataPath() : string{
		return $this->moduleDataPath;
	}

	public function getModules(){
		return $this->modules;
	}

	public function getModule(string $moduleName){
		foreach($this->modules as $module){
			if($module->getInfo()->getName() == $moduleName){
				return $module;
			}
		}
		return null;
	}

	public function loadModule(Module $module){
		if($module->isLoaded()){
			Logger::info("Module " . $module->getInfo()->getName() . " is already loaded");
		}else{
			Logger::info("Loading module " . $module->getInfo()->getName() . " v" . $module->getInfo()->getVersion());
			if($module->preLoad()){
				$module->setLoaded(true);
				$module->load();
			}else{
				Logger::info(TextFormat::RED . "Module " . $module->getInfo()->getName() . " v" . $module->getInfo()->getVersion() . " load failed.");
			}
		}
	}

	public function loadModuleDirectly(string $info, string $file){
		$info = new ModuleInfo($info, 0);
		$className = $info->getMain();
		$this->classLoader->addPath($file . "/src");
		if(is_a($className, Module::class, true) and
			class_exists($className, true) and !(new \ReflectionClass($className))->isAbstract()){
			$module = new $className($this, $info, $file);
		}else{
			$module = new FallbackLoader($this, $info, $file);
		}
		$this->loadModule($module);
	}

	public function getClassLoader() : \ClassLoader{
		return $this->classLoader;
	}
}
