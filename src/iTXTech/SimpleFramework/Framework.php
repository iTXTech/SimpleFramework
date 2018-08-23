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

namespace iTXTech\SimpleFramework;

use iTXTech\SimpleFramework\Console\CommandProcessor;
use iTXTech\SimpleFramework\Console\ConsoleReader;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleFramework\Module\ModuleManager;
use iTXTech\SimpleFramework\Module\WraithSpireMDR;
use iTXTech\SimpleFramework\Scheduler\OnCompletionListener;
use iTXTech\SimpleFramework\Scheduler\ServerScheduler;
use iTXTech\SimpleFramework\Util\Config;
use iTXTech\SimpleFramework\Util\Util;

class Framework implements OnCompletionListener{
	const PROG_NAME = "SimpleFramework";
	const PROG_VERSION = "2.1.0";
	const API_LEVEL = 6;
	const CODENAME = "Navi";

	/** @var Framework */
	private static $instance = null;

	/** @var ConsoleReader */
	private $console;

	/** @var CommandProcessor */
	private $commandProcessor;

	/** @var Config */
	private $config;

	/** @var \ClassLoader */
	private $classLoader;

	/** @var ServerScheduler */
	private $scheduler;

	/** @var ModuleManager */
	private $moduleManager;

	private $shutdown = false;

	private $currentTick = 0;

	private $titleQueue = [];

	private $dataPath;
	private $modulePath;
	private $moduleDataPath;

	private $commandLineOnly = false;
	public $displayTitle = true;

	public static $usleep = 50000;

	public function __construct(\ClassLoader $classLoader, array $argv){
		if(self::$instance === null){
			self::$instance = $this;
		}
		$this->dataPath = \getcwd() . DIRECTORY_SEPARATOR;
		$this->modulePath = $this->dataPath . "modules" . DIRECTORY_SEPARATOR;
		$this->moduleDataPath = $this->dataPath . "data" . DIRECTORY_SEPARATOR;
		$this->classLoader = $classLoader;
		$this->start($argv);
	}

	public function getLoader(){
		return $this->classLoader;
	}

	public function getName() : string {
		return self::PROG_NAME;
	}

	public function getVersion() : string {
		return self::PROG_VERSION;
	}

	public function getCodename() : string {
		return self::CODENAME;
	}

	public function getAPILevel() : int{
		return self::API_LEVEL;
	}

	public static function getInstance() : Framework{
		return self::$instance;
	}

	public static function isStarted(): bool{
		return self::$instance !== null;
	}

	public function getScheduler() : ServerScheduler{
		return $this->scheduler;
	}

	public function getModuleManager(): ModuleManager{
		return $this->moduleManager;
	}

	private function processCommandLineOptions(array $argv) : bool{
		foreach($argv as $c => $arg){
			switch($arg){
				case "-s":
					$this->commandLineOnly = true;
					break;
				case "-v":
					Logger::info(self::PROG_NAME . ' version "' . self::PROG_VERSION . '"');
					Logger::info("SFCLI API Level " . self::API_LEVEL . " [" . self::CODENAME . "]");
					break;
				case "-c":
					Logger::$noColor = true;
					break;
				case "-w":
					Logger::$fullDisplay = false;
					break;
				case "-h":
					Logger::info("Usage: sfcli");
					Logger::info("       sfcli [options]");
					Logger::info("  -a           No command line output.");
					Logger::info("  -c           Display in no color mode.");
					Logger::info("  -e [COMMAND] Execute a registered command.");
					Logger::info("  -f [FILE]    Load a module.");
					Logger::info("  -h           Display this message.");
					Logger::info("  -l [FILE]    Log to a specified file, only work with -s .");
					Logger::info("  -s           Execute in pure command line mode.");
					Logger::info("  -t           Do not display title.");
					Logger::info("  -v           Display version of this program.");
					Logger::info("  -w           Logger without time and prefix.");
					break;
				case "-a":
					Logger::$noOutput = true;
					break;
				case "-f":
					if(!isset($argv[$c + 1])){
						Logger::error("Module file not found.");
						break;
					}
					$file = $argv[$c + 1];
					if(!file_exists($file)){
						Logger::error("Module file not found.");
						break;
					}
					$this->moduleManager = new ModuleManager($this->classLoader, "", "", $this->commandLineOnly);
					$this->moduleManager->tryLoadModule($file);
					break;
				case "-l":
					if(!isset($argv[$c + 1])){
						Logger::error("No input file.");
						break;
					}
					Logger::setLogFile($argv[$c + 1]);
					break;
				case "-e":
					if(!isset($argv[$c + 1])){
						Logger::error("No input command.");
						break;
					}
					$this->commandProcessor = new CommandProcessor();
					$this->commandProcessor->dispatchCommand($argv[$c + 1]);
					break;
				case "-t":
					$this->displayTitle = false;
					break;
			}
		}

		if(in_array("-s", $argv)){
			return true;
		}
		return false;
	}

	private function start(array $argv){
		try{
			if(!$this->processCommandLineOptions($argv)){
				$this->displayTitle("SimpleFramework is starting...");
				@mkdir("modules");
				@mkdir("data");

				set_exception_handler("\\iTXTech\\SimpleFramework\\Console\\Logger::logException");

				$this->config = new Config($this->dataPath . "config.json", Config::JSON, [
					"auto-load-modules" => true,
					"async-workers" => 2,
					"log-file" => "",
					"display-title" => true,
					"module-dependency-resolver" => [
						"enabled" => true,
						"remote-database" => "https://raw.githubusercontent.com/iTXTech/WraithSpireDatabase/master/",
						"modules" => []
					]
				]);
				$this->config->save();

				Logger::setLogFile($this->config->get("log-file", ""));

				Logger::info(TextFormat::AQUA . self::PROG_NAME . " " . TextFormat::LIGHT_PURPLE . self::PROG_VERSION . TextFormat::GREEN . " [" . self::CODENAME . "]");
				Logger::info(TextFormat::GOLD . "Licensed under GNU General Public License v3.0");

				if(!\iTXTech\SimpleFramework\SINGLE_THREAD){
					Logger::info("Starting Console Daemon...");
					$this->console = new ConsoleReader();
				}

				Logger::info("Starting Command Processor...");
				if(!$this->commandProcessor instanceof CommandProcessor){
					$this->commandProcessor = new CommandProcessor();
				}

				Logger::info("Starting multi-threading scheduler...");
				$this->scheduler = new ServerScheduler($this->classLoader, $this, $this->config->get("async-workers", 2));

				if($this->moduleManager === null){
					$this->moduleManager = new ModuleManager($this->classLoader, $this->modulePath, $this->moduleDataPath, $this->commandLineOnly);
				}

				$mdr = $this->config->get("module-dependency-resolver");
				if($mdr["enabled"]){
					Logger::info("Starting WraithSpire module dependency resolver...");
					$this->moduleManager->registerModuleDependencyResolver(
						new WraithSpireMDR($this->moduleManager, $mdr["remote-database"], $mdr["modules"]));
				}

				if($this->config->get("auto-load-modules", true)){
					$this->moduleManager->loadModules();
				}

				$this->displayTitle = $this->config->get("display-title", true);

				if(($mdr = $this->moduleManager->getModuleDependencyResolver()) instanceof WraithSpireMDR){
					/** @var WraithSpireMDR $mdr */
					$mdr->init();
				}

				Logger::notice("Done! Type 'help' for help.");

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

	//main thread tick, not recommend for modules
	private function tick(){
		while(!$this->shutdown){
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
			usleep(self::$usleep);
		}

		//shutdown!
		Logger::notice("Stopping SimpleFramework...");
		foreach($this->moduleManager->getModules() as $module){
			if($module->isLoaded()){
				$this->moduleManager->unloadModule($module);
			}
		}
		$this->config->save();
		$this->scheduler->cancelAllTasks();
		$this->scheduler->mainThreadHeartbeat(PHP_INT_MAX);
		$this->console->shutdown();
		$this->console->notify();
	}

	public static function getUptime(){
		return Util::formatTime(microtime(true) - \iTXTech\SimpleFramework\START_TIME);
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
			self::displayTitle("SimpleFramework" . $message);
		}
		$this->titleQueue = [];
	}

	public static function displayTitle(string $title){
		echo "\x1b]0;" . $title . "\x07";
	}
}