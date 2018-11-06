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
use iTXTech\SimpleFramework\Framework;
use iTXTech\SimpleFramework\Util\Util;

abstract class Module{
	/** @var Framework */
	protected $framework;

	/** @var ModuleInfo */
	private $info;

	/** @var ModuleManager */
	private $manager;

	private $file;
	private $dataFolder;
	private $loaded = false;

	public final function __construct(ModuleManager $manager, ModuleInfo $info, string $file){
		$this->file = $file . DIRECTORY_SEPARATOR;
		$this->manager = $manager;
		$this->framework = Framework::getInstance();
		$this->info = $info;
		$this->dataFolder = $manager->getModuleDataPath() . $info->getName() . DIRECTORY_SEPARATOR;
	}

	public function getDataFolder() : string{
		return $this->dataFolder;
	}

	public final function setLoaded(bool $loaded){
		$this->loaded = $loaded;
	}

	public final function getFramework() : Framework{
		return $this->framework;
	}

	public function preLoad() : bool{
		if($this->info->getAPILevel() > Framework::API_LEVEL){
			Logger::error("Module requires API: " . $this->info->getAPILevel() . " Current API: " . Framework::API_LEVEL);
			return false;
		}
		if($this->checkExtensions()){
			return (($resolver = $this->manager->getModuleDependencyResolver()) instanceof ModuleDependencyResolver) ?
				$resolver->resolveDependencies($this) : $this->checkDependencies();
		}
		return false;
	}

	protected function checkDependencies() : bool{
		$dependencies = $this->info->getDependencies();
		foreach($dependencies as $dependency){
			$name = $dependency["name"];
			if(strstr($name, "/")){
				$name = explode("/", $name, 2);
				$name = end($name);
			}
			$error = false;
			if(isset($dependency["version"])){
				if(!($module = $this->manager->getModule($name)) instanceof Module){
					$error = true;
				}else{
					$error = Util::compareVersion($dependency["version"], $module->getInfo()->getVersion());
				}
			}
			if($error == true){
				Logger::error("Module " . '"' . $this->getName() . '"' . " requires module " . '"' . $name . '"' .
					" version " . ($dependency["version"] ?? "Unspecified"));
				return false;
			}
		}
		return true;
	}

	protected function checkExtensions() : bool{
		$extensions = $this->info->getExtensions();
		foreach($extensions as $extension){
			$error = true;
			if(extension_loaded($extension["name"])){
				if(isset($extension["version"])){
					$extVer = (new \ReflectionExtension($extension["name"]))->getVersion();
					$error = Util::compareVersion($extension["version"], $extVer);
				}else{
					$error = false;
				}
			}
			if($error){
				Logger::error("Module " . '"' . $this->getName() . '"' . " requires extension " . '"' . $extension["name"] . '"' .
					" version " . ($extension["version"] ?? "Unspecified"));
				return false;
			}
		}
		return true;
	}

	public abstract function load();

	public abstract function unload();

	public final function isLoaded() : bool{
		return $this->loaded;
	}

	public function doTick(int $currentTick){
	}

	public final function getInfo() : ModuleInfo{
		return $this->info;
	}

	public final function getName() : string{
		return $this->info->getName();
	}

	public function getResource($filename){
		$filename = rtrim(str_replace("\\", "/", $filename), "/");
		if(file_exists($this->file . "resources/" . $filename)){
			return fopen($this->file . "resources/" . $filename, "rb");
		}

		return null;
	}

	/**
	 * @param string $filename
	 * @param bool $replace
	 *
	 * @return bool
	 */
	public function saveResource($filename, $replace = false){
		if(trim($filename) === ""){
			return false;
		}

		if(($resource = $this->getResource($filename)) === null){
			return false;
		}

		$out = $this->dataFolder . $filename;
		if(!file_exists(dirname($out))){
			mkdir(dirname($out), 0755, true);
		}

		if(file_exists($out) and $replace !== true){
			return false;
		}

		$ret = stream_copy_to_stream($resource, $fp = fopen($out, "wb")) > 0;
		fclose($fp);
		fclose($resource);
		return $ret;
	}

	/**
	 * Returns all the resources packaged with the plugin
	 *
	 * @return string[]
	 */
	public function getResources(){
		$resources = [];
		if(is_dir($this->file . "resources/")){
			foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->file . "resources/")) as $resource){
				$resources[] = $resource;
			}
		}

		return $resources;
	}

	public function getFile() : string{
		return $this->file;
	}
}
