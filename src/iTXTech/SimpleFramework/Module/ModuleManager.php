<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2022 iTX Technologies
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
use iTXTech\SimpleFramework\Util\Util;

class ModuleManager{
	/** @var Module[] */
	public $modules = [];

	/** @var ModuleDependencyResolver */
	private $moduleDependencyResolver;

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

	public function unloadModule(Module $module){
		if($module->isLoaded()){
			Logger::info("Unloading module " . $module->getInfo()->getName() . " v" . $module->getInfo()->getVersion());
			$module->unload();
			$module->setLoaded(false);
		}else{
			Logger::info("Module " . $module->getInfo()->getName() . " is not loaded.");
		}
	}

	public function loadModules(){
		/** @var Module[] $modules */
		$modules = [];
		foreach(new \RegexIterator(new \DirectoryIterator($this->modulePath), "/\\.phar$/i") as $file){
			if($file === "." or $file === ".."){
				continue;
			}
			$this->tryLoadPackageModule($this->modulePath . $file, $modules);
		}
		foreach(new \RegexIterator(new \DirectoryIterator($this->modulePath), "/[^\\.]/") as $file){
			if($file === "." or $file === ".."){
				continue;
			}
			$this->tryLoadSourceModule($this->modulePath . $file, $modules);
		}
		foreach(self::sortModule($modules) as $module){
			$this->modules[$module->getInfo()->getName()] = $module;
			$this->loadModule($module);
		}
	}

	public function tryLoadModule(string $file) : bool{
		/** @var Module[] $modules */
		$modules = [];
		if(!$this->tryLoadSourceModule($file, $modules)){
			$this->tryLoadPackageModule($file, $modules);
		}
		foreach(self::sortModule($modules) as $module){
			$this->modules[$module->getInfo()->getName()] = $module;
			$this->loadModule($module);
			return true;
		}
		return false;
	}

	private static function sortModule(array $modules) : array{
		/** @var Module[] $modules */
		$m = [];
		foreach($modules as $module){
			$d = [];
			foreach($module->getInfo()->getDependencies() as $dependency){
				$n = explode("/", $dependency["name"]);
				$n = end($n);
				$d[] = $n;
			}
			$m[$module->getName()] = $d;
		}
		$resolved = [];
		$unresolved = [];
		foreach(array_keys($m) as $table){
			try{
				[$resolved, $unresolved] = Util::depResolve($table, $m, $resolved, $unresolved);
			}catch(\Throwable $e){
				Logger::logException($e);
			}
		}
		$m = [];
		foreach($resolved as $name){
			if(isset($modules[$name])){
				$m[$name] = $modules[$name];
			}
		}
		return $m;
	}

	public function tryLoadPackageModule(string $file, array &$modules) : bool{
		if(pathinfo($file, PATHINFO_EXTENSION) != "phar"){
			return false;
		}
		$phar = new \Phar($file);
		foreach(ModuleInfo::ACCEPTABLE_MANIFEST_FILENAME as $name){
			if(isset($phar[$name])){
				break;
			}
		}
		if(isset($phar[$name])){
			$info = $phar[$name];
			if($info instanceof \PharFileInfo){
				$this->loadModuleInternal($info->getContent(), ModuleInfo::LOAD_METHOD_PACKAGE, "phar://$file", $modules);
				return true;
			}
		}
		return false;
	}

	public function tryLoadSourceModule(string $file, array &$modules) : bool{
		if(is_dir($file)){
			foreach(ModuleInfo::ACCEPTABLE_MANIFEST_FILENAME as $name){
				if(file_exists($file . "/" . $name)){
					$info = @file_get_contents($file . "/" . $name);
					if($info != ""){
						$this->loadModuleInternal($info, ModuleInfo::LOAD_METHOD_SOURCE, $file, $modules);
					}
					return true;
				}
			}
			if(file_exists($i = $file . "/sf.json.php")){
				$this->loadModuleInternal(require($i), ModuleInfo::LOAD_METHOD_SOURCE, $file, $modules);
				return true;
			}
		}
		return false;
	}

	private function loadModuleInternal(string $info, int $method, string $file, array &$modules){
		$info = new ModuleInfo($info, $method);
		$className = $info->getMain();
		$this->classLoader->addPath($file . "/src");
		if(is_a($className, Module::class, true) and
			class_exists($className, true) and !(new \ReflectionClass($className))->isAbstract()){
			$module = new $className($this, $info, $file);
		}else{
			$module = new FallbackLoader($this, $info, $file);
		}
		$modules[$info->getName()] = $module;
	}

	public function registerModuleDependencyResolver(ModuleDependencyResolver $resolver){
		$this->moduleDependencyResolver = $resolver;
	}

	public function getModuleDependencyResolver(){
		return $this->moduleDependencyResolver;
	}

	public function getClassLoader() : \ClassLoader{
		return $this->classLoader;
	}
}
