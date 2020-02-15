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

namespace iTXTech\SimpleFramework;

use iTXTech\SimpleFramework\Module\ModuleManager;
use iTXTech\SimpleFramework\Util\Config;
use iTXTech\SimpleFramework\Util\FrameworkProperties;
use iTXTech\SimpleFramework\Util\StringUtil;

class Framework{
	public const PROG_NAME = "SimpleFramework";
	public const PROG_VERSION = "2.2.0_PeachPie";
	public const API_LEVEL = 7;
	public const CODENAME = "Centaur";

	/** @var Framework */
	private static $instance = null;

	private static $tickInterval = 50000;

	private $startEntry;
	/** @var Config */
	private $config;
	/** @var \ClassLoader */
	private $classLoader;
	/** @var ModuleManager */
	private $moduleManager;

	public function __construct(\ClassLoader $classLoader){
		if(self::$instance === null){
			self::$instance = $this;
		}
		$this->classLoader = $classLoader;
	}


	public static function getTickInterval() : int{
		return self::$tickInterval;
	}

	public function getLoader(){
		return $this->classLoader;
	}

	public function getName() : string{
		return self::PROG_NAME;
	}

	public function getVersion() : string{
		return self::PROG_VERSION;
	}

	public function getCodename() : string{
		return self::CODENAME;
	}

	public function getApi() : int{
		return self::API_LEVEL;
	}

	public static function getInstance() : ?Framework{
		return self::$instance;
	}

	public static function isStarted() : bool{
		return self::$instance !== null;
	}

	public function getModuleManager() : ?ModuleManager{
		return $this->moduleManager;
	}

	public function getStartEntry() : string{
		return $this->startEntry;
	}

	public function processPreload(array $argv) : array{
		$this->startEntry = array_shift($argv);
		while(isset($argv[0]) and StringUtil::startsWith($argv[0], "p=")){
			$preload = substr(array_shift($argv), strlen("p="));
			if(file_exists($preload)){
				require_once $preload;
			}
		}
		return $argv;
	}
}
