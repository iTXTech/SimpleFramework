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

namespace iTXTech\SimpleFramework;

use iTXTech\SimpleFramework\Console\CmdLineOpt\CmdLineOpt;
use iTXTech\SimpleFramework\Console\CommandProcessor;
use iTXTech\SimpleFramework\Console\ConsoleReader;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Option\HelpFormatter;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleFramework\Module\ModuleManager;
use iTXTech\SimpleFramework\Module\WraithSpireMDR;
use iTXTech\SimpleFramework\Scheduler\OnCompletionListener;
use iTXTech\SimpleFramework\Scheduler\Scheduler;
use iTXTech\SimpleFramework\Util\Config;
use iTXTech\SimpleFramework\Util\FrameworkProperties;
use iTXTech\SimpleFramework\Util\Platform\Platform;
use iTXTech\SimpleFramework\Util\StringUtil;
use iTXTech\SimpleFramework\Util\Util;

class Framework implements OnCompletionListener{
	public const PROG_NAME = "SimpleFramework";
	public const PROG_VERSION = "2.3.0";
	public const API_LEVEL = 8;
	public const CODENAME = "Centaur";

	/** @var Framework */
	private static $instance = null;

	private static $tickInterval = 50000;

	private $startEntry;
	/** @var ConsoleReader */
	private $console;
	/** @var CommandProcessor */
	private $commandProcessor;
	/** @var Config */
	private $config;
	/** @var \ClassLoader */
	private $classLoader;
	/** @var Scheduler */
	private $scheduler;
	/** @var ModuleManager */
	private $moduleManager;
	/** @var Options */
	private $options;
	/** @var FrameworkProperties */
	private $properties;

	//Properties
	private $shutdown = false;
	private $displayTitle = true;

	//
	private $currentTick = 0;
	private $titleQueue = [];

	public function __construct(\ClassLoader $classLoader){
		if(self::$instance === null){
			self::$instance = $this;
		}
		$this->classLoader = $classLoader;
		$this->options = new Options();
		$this->registerDefaultOptions();

		$this->properties = new FrameworkProperties();
		$this->properties->dataPath = \getcwd() . DIRECTORY_SEPARATOR;
		$this->properties->generatePath();
	}

	public static function registerExceptionHandler(){
		set_exception_handler([Logger::class, "logException"]);
		set_error_handler([Logger::class, "errorExceptionHandler"]);
	}

	public function getProperties() : FrameworkProperties{
		return $this->properties;
	}

	public static function getTickInterval() : int{
		return self::$tickInterval;
	}

	public static function setTickInterval(int $tickInterval) : void{
		self::$tickInterval = max($tickInterval, 0);
	}

	public function isDisplayTitle() : bool{
		return $this->displayTitle;
	}

	public function setDisplayTitle(bool $displayTitle) : void{
		$this->displayTitle = $displayTitle;
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

	public static function init(){
		Platform::init();
		self::registerExceptionHandler();
	}

	public function getScheduler() : ?Scheduler{
		return $this->scheduler;
	}

	public function getModuleManager() : ?ModuleManager{
		return $this->moduleManager;
	}

	public function getStartEntry() : string{
		return $this->startEntry;
	}

	private function registerDefaultOptions(){
		//FREE SWITCHES
		//i, j, k
		//p, q
		//t, u, w, x, y, z
		CmdLineOpt::regAll();
	}

	public function processCommandLineOptions(array $argv){
		try{
			CmdLineOpt::init($this->options);
			CmdLineOpt::processAll($argv, $this->options);
		}catch(\Throwable $e){
			Util::println($e->getMessage());
			$t = (new HelpFormatter())->generateHelp("sf", $this->options);
			echo $t;
			exit(1);
		}
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

	public function start(bool $useMainThreadTick = true){
		try{
			$this->properties->mkdirDirs();

			$this->config = new Config($this->properties->configPath, Config::JSON, [
				"auto-load-modules" => true,
				"async-workers" => 2,
				"log-file" => "",
				"log-level" => Logger::INFO,
				"display-title" => true,
				"wsmdr" => [//WraithSpireModuleDependencyResolver
					"enabled" => true,
					"remote-database" => "https://raw.githubusercontent.com/iTXTech/WraithSpireDatabase/master/",
					"modules" => []
				]
			]);
			$this->config->save();
			$this->properties->mergeConfig($this->config);

			Logger::setLogFile($this->config->get("log-file", ""));
			Logger::$logLevel = $this->config->get("log-level", 1);

			Logger::info(TextFormat::AQUA . self::PROG_NAME . " " . TextFormat::LIGHT_PURPLE .
				self::PROG_VERSION . TextFormat::GREEN . " [" . self::CODENAME . "]");
			Logger::info(TextFormat::GOLD . "Licensed under GNU General Public License v3.0");

			if($this->moduleManager === null){
				$this->moduleManager = new ModuleManager($this->classLoader,
					$this->properties->modulePath, $this->properties->moduleDataPath);
			}

			if(!SINGLE_THREAD){
				Logger::info("Starting ConsoleReader...");
				$this->console = new ConsoleReader();
			}

			Logger::info("Starting Command Processor...");
			$this->commandProcessor = new CommandProcessor();
			$this->commandProcessor->registerDefaultCommands();

			Logger::info("Starting multi-threading scheduler...");
			$this->scheduler = new Scheduler($this->classLoader, $this, $this->config->get("async-workers", 2));

			$mdr = $this->config->get("wsmdr");
			if($mdr["enabled"]){
				Logger::info("Starting WraithSpire module dependency resolver...");
				$this->moduleManager->registerModuleDependencyResolver(
					new WraithSpireMDR($this->moduleManager, $mdr["remote-database"], $mdr["modules"]));
			}

			if($this->config->get("auto-load-modules", true)){
				$this->moduleManager->loadModules();
			}
			$this->properties->loadModules($this->moduleManager);

			$this->displayTitle = $this->config->get("display-title", true);

			if(($mdr = $this->moduleManager->getModuleDependencyResolver()) != null){
				$mdr->init();
			}

			Logger::info("Done! Type 'help' for help.");

			$this->properties->runCommands($this->commandProcessor);

			if($useMainThreadTick){
				$this->tick();
			}
		}catch(\Throwable $e){
			Logger::logException($e);
		}
	}

	public function shutdown(){
		$this->shutdown = true;
	}

	public function getCommandProcessor() : CommandProcessor{
		return $this->commandProcessor;
	}

	public function tick(){
		while(!$this->shutdown){
			$this->update();
			usleep(self::$tickInterval);
		}

		//shutdown!
		$this->stop();
	}

	public function update(){
		$this->currentTick++;
		foreach($this->moduleManager->getModules() as $module){
			if($module->isLoaded()){
				$module->doTick($this->currentTick);
			}
		}
		$this->scheduler->mainThreadHeartbeat($this->currentTick);
		$this->checkConsole();
		if(($this->currentTick % 20) === 0){
			$this->combineTitle();
		}
	}

	public function stop(){
		Logger::info("Stopping " . self::PROG_NAME . "...");
		foreach($this->moduleManager->getModules() as $module){
			if($module->isLoaded()){
				$this->moduleManager->unloadModule($module);
			}
		}
		//$this->config->save();
		$this->scheduler->cancelAllTasks();
		$this->scheduler->mainThreadHeartbeat(PHP_INT_MAX);
		$this->console->shutdown();
		$this->console->notify();
	}

	public static function getUptime(){
		return Util::formatTime(microtime(true) - START_TIME);
	}

	private function checkConsole(){
		if(isset($this->console)){
			while(($line = $this->console->getLine()) != null){
				$this->commandProcessor->dispatchCommand($line);
			}
		}
	}

	public function addTitleBlock(string $prop, string $contents){
		$this->titleQueue[$prop] = $contents;
	}

	private function combineTitle(){
		if($this->displayTitle){
			$message = "";
			foreach($this->titleQueue as $prop => $contents){
				$message .= " | " . $prop . " " . $contents;
			}
			self::displayTitle(self::PROG_NAME . $message);
		}
		$this->titleQueue = [];
	}

	public static function displayTitle(string $title){
		echo "\x1b]0;" . $title . "\x07";
	}
}
