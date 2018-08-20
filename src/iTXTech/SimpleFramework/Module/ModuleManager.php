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

namespace iTXTech\SimpleFramework\Module;

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;

class ModuleManager{
	/** @var Module[] */
	public $modules = [];

	/** @var ModuleDependencyResolver */
	private $moduleDependencyResolver;

	/** @var \ClassLoader */
	private $classLoader;

	private $modulePath;
	private $moduleDataPath;
	private $cliOnly;

	public function __construct(\ClassLoader $loader, string $modulePath, string $moduleDataPath, bool $cliOnly = false){
		$this->classLoader = $loader;
		$this->modulePath = $modulePath;
		$this->moduleDataPath = $moduleDataPath;
		$this->cliOnly = $cliOnly;
	}

	public function setModulePath(string $modulePath): void{
		$this->modulePath = $modulePath;
	}

	public function setModuleDataPath(string $moduleDataPath): void{
		$this->moduleDataPath = $moduleDataPath;
	}

	public function getModulePath(): string{
		return $this->modulePath;
	}

	public function getModuleDataPath(): string{
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
			Logger::notice("Module " . $module->getInfo()->getName() . " is already loaded");
		}else{
			if(!$this->cliOnly){
				Logger::info("Loading module " . $module->getInfo()->getName() . " v" . $module->getInfo()->getVersion());
			}
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
			if(!$this->cliOnly){
				Logger::info("Unloading module " . $module->getInfo()->getName() . " v" . $module->getInfo()->getVersion());
			}
			$module->unload();
			$module->setLoaded(false);
		}else{
			Logger::notice("Module " . $module->getInfo()->getName() . " is not loaded.");
		}
	}

	public function loadModules(){
		$modules = [];
		for($i = ModuleInfo::LOAD_ORDER_MIN; $i <= ModuleInfo::LOAD_ORDER_MAX; $i++){
			$modules[$i] = [];
		}
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
		for($i = ModuleInfo::LOAD_ORDER_MIN; $i <= ModuleInfo::LOAD_ORDER_MAX; $i++){
			foreach($modules[$i] as $module){
				$this->modules[$module[0]] = $module[1];
				$this->loadModule($module[1]);
			}
		}
	}

	public function tryLoadModule(string $file): bool{
		$modules = [];
		for($i = ModuleInfo::LOAD_ORDER_MIN; $i <= ModuleInfo::LOAD_ORDER_MAX; $i++){
			$modules[$i] = [];
		}
		if(!$this->tryLoadSourceModule($file, $modules)){
			$this->tryLoadPackageModule($file, $modules);
		}
		foreach($modules as $order){
			foreach($order as $module){
				$this->modules[$module[0]] = $module[1];
				$this->loadModule($module[1]);
				return true;
			}
		}
		return false;
	}

	public function tryLoadPackageModule(string $file, array &$modules): bool{
		if(pathinfo($file, PATHINFO_EXTENSION) != "phar"){
			return false;
		}
		$phar = new \Phar($file);
		if(isset($phar["info.json"])){
			$info = $phar["info.json"];
			if($info instanceof \PharFileInfo){
				$file = "phar://$file";
				$info = new ModuleInfo($info->getContent(), ModuleInfo::LOAD_METHOD_PACKAGE);
				$className = $info->getMain();
				$this->classLoader->addPath($file . "/src");
				$class = new \ReflectionClass($className);
				if(is_a($className, Module::class, true) and !$class->isAbstract()){
					$module = new $className($this, $info, $file);
					$modules[$info->getLoadOrder()][] = [$info->getName(), $module];
					return true;
				}
			}
		}
		return false;
	}

	public function tryLoadSourceModule(string $file, array &$modules): bool{
		if(is_dir($file) and file_exists($file . "/info.json") and file_exists($file . "/src/")){
			if(is_dir($file) and file_exists($file . "/info.json")){
				$info = @file_get_contents($file . "/info.json");
				if($info != ""){
					$info = new ModuleInfo($info, ModuleInfo::LOAD_METHOD_SOURCE);
					$className = $info->getMain();
					$this->classLoader->addPath($file . "/src");
					$class = new \ReflectionClass($className);
					if(is_a($className, Module::class, true) and !$class->isAbstract()){
						$module = new $className($this, $info, $file);
						$modules[$info->getLoadOrder()][] = [$info->getName(), $module];
						return true;
					}
				}
			}
		}
		return false;
	}

	public function registerModuleDependencyResolver(ModuleDependencyResolver $resolver){
		$this->moduleDependencyResolver = $resolver;
	}

	public function getModuleDependencyResolver(){
		return $this->moduleDependencyResolver;
	}
}