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
use iTXTech\SimpleFramework\Framework;
use iTXTech\SimpleFramework\Util\Util;

class WraithSpireMDR implements ModuleDependencyResolver{
	private $database;
	/** @var ModuleManager */
	private $manager;
	private $modules;

	public function __construct(ModuleManager $manager, string $database, array $modules){
		$this->manager = $manager;
		$this->database = $database;
		$this->modules = $modules;
	}

	public function init(){
		if($this->modules != []){
			Logger::info(TextFormat::AQUA . "Resolving modules required by WraithSpire configuration.");
			$this->resolve($this->modules, "Configuration");
		}
	}

	private function getModuleData(string $vendor, string $name, string $version){
		$link = $this->database . "$vendor/$name/$version.json";
		$i = 1;
		while(!($result = Util::getURL($link))->isSuccessful() and $i <= 3){
			Logger::error("Obtaining module data for $vendor/$name version $version failed, retrying $i...");
			$i++;
		}
		if($result == false){
			Logger::error("Obtaining module data for $vendor/$name version $version failed, please check your network connection.");
			return false;
		}
		if(strstr($result, "404: Not Found")){
			Logger::error("Not found module data for $vendor/$name version $version .");
			return false;
		}
		return json_decode($result->getBody(), true);
	}

	public function downloadDependency(string $moduleName, string $name, string $version): bool{
		$vendor = "";
		if(strstr($name, "/")){
			$rName = explode("/", $name, 2);
			$name = $rName[1];
			$vendor = $rName[0];
		}
		if($vendor == ""){
			Logger::info(TextFormat::RED . $moduleName . " requires dependency $name does not have a vendor, please contact the author of the module or manually resolve its dependency.");
			return false;
		}
		if(($module = $this->manager->getModule($name)) instanceof Module){
			if($module->getInfo()->getLoadMethod() == ModuleInfo::LOAD_METHOD_SOURCE){
				Logger::info(TextFormat::RED . "Please manually remove the source folder of " . $module->getInfo()->getName() . " then the dependency resolver can download the specifying module.");
				return false;
			}
			$file = str_replace("phar://", "", substr($module->getFile(), 0, strlen($module->getFile()) - 1));
			rename($file, $file . ".old");
			Logger::info(TextFormat::AQUA . "You must restart this program after resolved dependencies.");
		}
		if(($data = $this->getModuleData($vendor, $name, $version)) !== false and ($data !== null)){
			if($data["api"] > Framework::API_LEVEL){
				Logger::info("$vendor/$name version $version requires API: " . $data["api"] . " Current API: " . Framework::API_LEVEL . ". Module may not work properly.");
			}
			$fileName = explode("/", $data["link"]);
			$fileName = end($fileName);
			Logger::info(TextFormat::AQUA . "Downloading module $vendor/$name v$version ...");
			Util::downloadFile($this->manager->getModulePath() . $fileName, $data["link"]);
			Logger::info(TextFormat::GREEN . "Module $vendor/$name v$version downloaded. Loading...");
			return $this->manager->tryloadModule($this->manager->getModulePath() . $fileName);
		}
		return false;
	}

	private function resolve(array $dependencies, string $moduleName): bool{
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
				Logger::info(TextFormat::GOLD . "Resolving dependency " . $name . " version " . $dependency["version"] . " @ " . $moduleName);
				if(!$this->downloadDependency($moduleName, $dependency["name"], $dependency["version"])
					and !($dependency["optional"] ?? false)){
					return false;
				}
			}
		}
		return true;
	}

	public function resolveDependencies(Module $module): bool{
		return $this->resolve($module->getInfo()->getDependencies(), $module->getInfo()->getName());
	}
}
