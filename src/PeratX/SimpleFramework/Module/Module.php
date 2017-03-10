<?php

/**
 * SimpleFramework
 * The fast, light-weighted, easy-to-extend php framework.
 *
 * Some classes are based on project PocketMine-MP.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PeratX
 */

namespace PeratX\SimpleFramework\Module;

use PeratX\SimpleFramework\Console\Logger;
use PeratX\SimpleFramework\Framework;

//Multi-thread is recommended for module design.
abstract class Module{
	/** @var Framework */
	protected $framework;
	private $loaded = false;

	/** @var ModuleInfo */
	private $info;

	private $file;

	private $dataFolder;

	public final function __construct(Framework $framework, ModuleInfo $info, string $file){
		$this->file = $file . DIRECTORY_SEPARATOR;
		$this->framework = $framework;
		$this->info = $info;
		$this->dataFolder = $framework->getModuleDataPath() . $info->getName() . DIRECTORY_SEPARATOR;
	}

	public function getDataFolder(): string{
		return $this->dataFolder;
	}

	public final function setLoaded(bool $loaded){
		$this->loaded = $loaded;
	}

	public final function getFramework(): Framework{
		return $this->framework;
	}

	public function preLoad(): bool{
		if($this->info->getAPILevel() > Framework::API_LEVEL){
			throw new \Exception("Module requires API Level: " . $this->info->getAPILevel() . " Current API Level: " . Framework::API_LEVEL);
		}
		return (($resolver = $this->framework->getModuleDependencyResolver()) instanceof ModuleDependencyResolver) ? $resolver->resolveDependency($this) : $this->checkDependency();
	}

	protected function checkDependency(){
		$dependencies = $this->info->getDependency();
		foreach($dependencies as $dependency){
			$name = $dependency["name"];
			if(strstr($name, "/")){
				$name = explode("/", $name, 2);
				$name = end($name);
			}
			$version = explode(".", $dependency["version"]);
			$error = false;
			if(count($version) != 3){
				$error = true;
			}
			if(($module = $this->framework->getModule($name)) instanceof Module){
				$targetVersion = explode(".", $module->getInfo()->getVersion());
				if(count($targetVersion) != 3){
					$error = true;
				}

				if($version[0] != $targetVersion[0]){
					$error = true;
				}elseif($version[1] > $targetVersion[1]){
					$error = true;
				}elseif($version[1] == $targetVersion[1] and $version[2] > $targetVersion[2]){
					$error = true;
				}
			}else{
				$error = true;
			}
			if($error == true){
				Logger::error("Module " . '"' . $this->getName() . '"' . " requires dependency module " . '"' . $name . '"' . " version " . $dependency["version"]);
				return false;
			}
		}
		return true;
	}

	public abstract function load();

	public abstract function unload();

	public final function isLoaded(): bool{
		return $this->loaded;
	}

	public function doTick(int $currentTick){
	}

	public final function getInfo(): ModuleInfo{
		return $this->info;
	}

	public final function getName(): string{
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
	 * @param bool   $replace
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

	public function getFile(): string{
		return $this->file;
	}
}