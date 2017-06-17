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
 * @author iTXTech
 * @link https://itxtech.org
 */

namespace iTXTech\SimpleFramework;

use iTXTech\SimpleFramework\Console\CommandProcessor;
use iTXTech\SimpleFramework\Console\ConsoleReader;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleFramework\Module\Module;
use iTXTech\SimpleFramework\Module\ModuleDependencyResolver;
use iTXTech\SimpleFramework\Module\ModuleInfo;
use iTXTech\SimpleFramework\Module\WraithSpireMDR;
use iTXTech\SimpleFramework\Scheduler\ServerScheduler;
use iTXTech\SimpleFramework\Util\Config;

class Framework{
	const PROG_NAME = "SimpleFramework";
	const PROG_VERSION = "2.0.0-beta.2";
	const API_LEVEL = 4;
	const CODENAME = "RYZEN";

	/** @var Framework */
	private static $obj = null;

	/** @var ConsoleReader */
	private $console;

	/** @var CommandProcessor */
	private $commandProcessor;

	/** @var Module[] */
	public $modules = [];

	/** @var Config */
	private $config;

	/** @var \ClassLoader */
	private $classLoader;

	/** @var ServerScheduler */
	private $scheduler;

	/** @var ModuleDependencyResolver */
	private $moduleDependencyResolver;

	private $shutdown = false;

	private $currentTick = 0;

	private $titleQueue = [];

	private $dataPath;
	private $modulePath;
	private $moduleDataPath;

	private $commandLineOnly = false;
	private $displayTitle = true;

	public function __construct(\ClassLoader $classLoader, array $argv){
		if(self::$obj === null){
			self::$obj = $this;
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

	public function getModuleDataPath() : string{
		return $this->moduleDataPath;
	}

	public function getModulePath() : string {
		return $this->modulePath;
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
		return self::$obj;
	}

	public function getScheduler() : ServerScheduler{
		return $this->scheduler;
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
			if(!$this->commandLineOnly){
				Logger::info("Loading module " . $module->getInfo()->getName() . " v" . $module->getInfo()->getVersion());
			}
			if($module->preLoad()){
				$module->load();
				$module->setLoaded(true);
			}else{
				Logger::info(TextFormat::RED . "Module " . $module->getInfo()->getName() . " v" . $module->getInfo()->getVersion() . " load failed.");
			}
		}
	}

	public function unloadModule(Module $module){
		if($module->isLoaded()){
			if(!$this->commandLineOnly){
				Logger::info("Unloading module " . $module->getInfo()->getName() . " v" . $module->getInfo()->getVersion());
			}
			$module->unload();
			$module->setLoaded(false);
		}else{
			Logger::notice("Module " . $module->getInfo()->getName() . " is not loaded.");
		}
	}

	private function loadModules(){
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

	public function tryLoadModule(string $file) : bool{
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

	public function tryLoadPackageModule(string $file, array &$modules) : bool{
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

	public function tryLoadSourceModule(string $file, array &$modules) : bool{
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
					$this->tryLoadModule($file);
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
					$this->commandProcessor = new CommandProcessor($this);
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

				Logger::info("Starting Console Daemon...");
				$this->console = new ConsoleReader();

				Logger::info("Starting Command Processor...");
				if(!$this->commandProcessor instanceof CommandProcessor){
					$this->commandProcessor = new CommandProcessor($this);
				}

				Logger::info("Starting multi-threading scheduler...");
				ServerScheduler::$WORKERS = $this->config->get("async-workers", 2);
				$this->scheduler = new ServerScheduler();

				$mdr = $this->config->get("module-dependency-resolver");
				if($mdr["enabled"]){
					Logger::info("Starting WraithSpire module dependency resolver...");
					$this->registerModuleDependencyResolver(new WraithSpireMDR($this, $mdr["remote-database"], $mdr["modules"]));
				}

				if($this->config->get("auto-load-modules", true)){
					$this->loadModules();
				}

				$this->displayTitle = $this->config->get("display-title", true);

				if($this->moduleDependencyResolver instanceof WraithSpireMDR){
					$this->moduleDependencyResolver->init();
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
			foreach($this->modules as $module){
				if($module->isLoaded()){
					$module->doTick($this->currentTick);
				}
			}
			$this->scheduler->mainThreadHeartbeat($this->currentTick);
			$this->checkConsole();
			if(($this->currentTick % 20) === 0){
				$this->combineTitle();
			}
			usleep(5);
		}

		//shutdown!
		Logger::notice("Stopping SimpleFramework...");
		foreach($this->modules as $module){
			if($module->isLoaded()){
				$this->unloadModule($module);
			}
		}
		$this->config->save();
		$this->scheduler->cancelAllTasks();
		$this->scheduler->mainThreadHeartbeat(PHP_INT_MAX);
		$this->console->shutdown();
		$this->console->notify();
	}

	private function checkConsole(){
		while(($line = $this->console->getLine()) != null){
			$this->commandProcessor->dispatchCommand($line);
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