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
use iTXTech\SimpleFramework\Framework;

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

	public function getManager() : ModuleManager{
		return $this->manager;
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
		if($this->info->getApi() > Framework::API_LEVEL){
			Logger::error("Module requires API: " . $this->info->getApi() . " Current API: " . Framework::API_LEVEL);
			return false;
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

	public function getFile() : string{
		return $this->file;
	}

	public function getResourceAsText(string $file){
		$file = rtrim(str_replace("\\", "/", $file), "/");
		if(file_exists($f = $this->file . "resources/" . $file . ".php")){
			return require($f);
		}
		return null;
	}
}
