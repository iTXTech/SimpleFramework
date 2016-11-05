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

namespace sf\module;

use sf\SimpleFramework;

//Multi-thread is recommended for plugin design.
abstract class Module{
	/** @var SimpleFramework */
	protected $framework;
	private $loaded = false;

	/** @var ModuleInfo */
	private $info;

	private $file;

	private $dataFolder;

	public final function __construct(SimpleFramework $framework, ModuleInfo $info, string $file){
		$this->file = $file;
		$this->framework = $framework;
		$this->info = $info;
		$this->dataFolder = $framework->getModuleDataPath() . $info->getName();
	}

	public function getDataFolder() : string{
		return $this->dataFolder;
	}

	public final function setLoaded(bool $loaded){
		$this->loaded = $loaded;
	}

	public final function preLoad() :bool{
		if($this->info->getAPILevel() > SimpleFramework::API_LEVEL){
			throw new \Exception("Plugin requires API Level: " . $this->info->getAPILevel() . " Current API Level: " . SimpleFramework::API_LEVEL);
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

}