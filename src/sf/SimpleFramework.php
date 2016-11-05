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

/*
 * Only work in Command Line environment
 */

namespace sf;

use sf\console\CommandProcessor;
use sf\console\ConsoleReader;
use sf\console\Logger;
use sf\console\Terminal;
use sf\console\TextFormat;
use sf\module\Module;
use sf\module\ModuleInfo;
use sf\util\Config;

class SimpleFramework{
	const PROG_NAME = "SimpleFrameworkCLI";
	const PROG_VERSION = "1.0.0-beta";
	const API_LEVEL = 1;
	const CODENAME = "Blizzard";

	/** @var SimpleFramework */
	private static $obj = null;

	/** @var ConsoleReader */
	private $console;

	/** @var CommandProcessor */
	private $commandProcessor;

	/** @var Module[] */
	private $modules = [];

	/** @var Config */
	private $config;

	/** @var \ClassLoader */
	private $classLoader;

	private $shutdown = false;

	private $currentTick = 0;

	private $titleQueue = [];

	private $dataPath;
	private $modulePath;
	private $moduleDataPath;

	public function __construct(\ClassLoader $classLoader){
		if(self::$obj === null){
			self::$obj = $this;
		}
		$this->dataPath = \getcwd() . DIRECTORY_SEPARATOR;
		$this->modulePath = $this->dataPath . "modules" . DIRECTORY_SEPARATOR;
		$this->moduleDataPath = $this->dataPath . "data" . DIRECTORY_SEPARATOR;
		$this->classLoader = $classLoader;
		$this->start();
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

	public static function getInstance() : SimpleFramework{
		return self::$obj;
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
			Logger::info("Loading Module " . $module->getInfo()->getName() . " v" . $module->getInfo()->getVersion());
			if($module->preLoad()){
				$module->load();
				$module->setLoaded(true);
			}
		}
	}

	public function unloadModule(Module $module){
		if($module->isLoaded()){
			Logger::info("Unloading module " . $module->getInfo()->getName() . " v" . $module->getInfo()->getVersion());
			$module->unload();
			$module->setLoaded(false);
		}else{
			Logger::notice("Module " . $module->getInfo()->getName() . " is not loaded.");
		}
	}

	private function loadModules(){
		foreach(new \RegexIterator(new \DirectoryIterator($this->modulePath), "/\\.phar$/i") as $file){
			if($file === "." or $file === ".."){
				continue;
			}
			$this->tryLoadPackageModule($file);
		}
		foreach(new \RegexIterator(new \DirectoryIterator($this->modulePath), "/[^\\.]/") as $file){
			if($file === "." or $file === ".."){
				continue;
			}
			$this->tryLoadSourceModule($file);
		}
	}

	public function tryLoadPackageModule(string $file) : bool{
		$file = $this->modulePath . $file;
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
					$this->modules[$class->getShortName()] = $module;
					$this->loadModule($module);
					return true;
				}
			}
		}
		return false;
	}

	public function tryLoadSourceModule(string $file) : bool{
		$file = $this->modulePath . $file;
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
						$this->modules[$class->getShortName()] = $module;
						$this->loadModule($module);
						return true;
					}
				}
			}
		}
		return false;
	}

	public function start(){
		try{
			$this->displayTitle("SimpleFramework is starting...");
			@mkdir("modules");
			@mkdir("data");
			$this->config = new Config($this->dataPath . "config.json", Config::JSON, [
				"auto-load-modules" => true
			]);
			$this->config->save();
			Logger::info(TextFormat::AQUA . self::PROG_NAME . " " . TextFormat::LIGHT_PURPLE . self::PROG_VERSION . TextFormat::GREEN . " [" . self::CODENAME . "]");
			Logger::info(TextFormat::GOLD . "Licensed under GNU General Public License v3.0");
			Logger::info("Starting Console Daemon...");
			$this->console = new ConsoleReader();
			Logger::info("Starting Command Processor...");
			$this->commandProcessor = new CommandProcessor($this);
			if($this->config->get("auto-load-modules", true)){
				$this->loadModules();
			}
			Logger::notice("Done! Type 'help' for help.");
			$this->tick();
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

	//main thread tick, not recommend for plugins
	public function tick(){
		while(!$this->shutdown){
			$this->currentTick++;
			foreach($this->modules as $module){
				if($module->isLoaded()){
					$module->doTick($this->currentTick);
				}
			}
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
		$this->console->shutdown();
		Logger::info("SimpleFramework is stopped.");
	}

	public function checkConsole(){
		while(($line = $this->console->getLine()) != null){
			$this->commandProcessor->dispatchCommand($line);
		}
	}

	public function addTitleBlock(string $prop, string $contents){
		$this->titleQueue[$prop] = $contents;
	}

	private function combineTitle(){
		$message = "";
		foreach($this->titleQueue as $prop => $contents){
			$message .= " | " . $prop . " " . $contents;
		}
		$this->displayTitle("SimpleFramework" . $message);
		$this->titleQueue = [];
	}

	private function displayTitle(string $title){
		echo "\x1b]0;" . $title . "\x07";
	}
}

//Base load

if(\Phar::running(true) !== ""){
	@define('sf\PATH', \Phar::running(true) . "/");
}else{
	@define('sf\PATH', \getcwd() . DIRECTORY_SEPARATOR);
}

require_once(\sf\PATH . "src/sf/util/ClassLoader.php");

$classLoader = new \ClassLoader();
$classLoader->addPath(\sf\PATH . "src");
$classLoader->register(true);

Terminal::init();

new SimpleFramework($classLoader);