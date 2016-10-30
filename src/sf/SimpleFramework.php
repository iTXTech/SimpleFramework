<?php

/**
 * SimpleFramework
 * The fast, light-weighted, easy-to-extend php framework.
 *
 * Some classes are based on project PocketMine-MP.
 * List: ConsoleReader, Terminal, TextFormat, Logger, Util, Config, ClassLoader
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PeratX
 */

namespace sf;

use sf\console\CommandProcessor;
use sf\console\ConsoleReader;
use sf\console\Logger;
use sf\console\Terminal;
use sf\console\TextFormat;
use sf\module\Module;
use sf\util\Config;
use sf\util\Util;

class SimpleFramework{
	const PROG_VERSION = "1.0.0-alpha-20161029";
	const API_LEVEL = 1;

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

	public function __construct(\ClassLoader $classLoader){
		if(self::$obj === null){
			self::$obj = $this;
		}
		$this->dataPath = \getcwd() . DIRECTORY_SEPARATOR;
		$this->classLoader = $classLoader;
		$this->start();
	}

	public static function getInstance() : SimpleFramework{
		return self::$obj;
	}

	public function getModules(){
		return $this->modules;
	}

	public function loadModule(Module $module){
		if($module->isLoaded()){
			Logger::notice("Module " . $module->getName() . " is already loaded");
		}else{
			Logger::info("Loading Module " . $module->getName() . " v" . $module->getVersion());
			$module->load();
		}
	}

	public function unloadModule(Module $module){
		if($module->isLoaded()){
			Logger::info("Unloading module " . $module->getName() . " v" . $module->getVersion());
			$module->unload();
		}else{
			Logger::notice("Module " . $module->getName() . " is not loaded.");
		}
	}

	public function registerModule($className) : bool{
		$class = new \ReflectionClass($className);
		if(is_a($className, Module::class, true) and !$class->isAbstract()){
			$module = new $className($this);
			$this->modules[$class->getShortName()] = $module;
			$this->loadModule($module);
			return true;
		}

		return false;
	}

	public function readModule(string $name, string $main){
		Logger::info("Reading $name");
		$path = $name . DIRECTORY_SEPARATOR . "src";
		$this->classLoader->addPath(\sf\PATH . "modules" . DIRECTORY_SEPARATOR . $path);
		$this->registerModule($main);
	}

	private function loadModules(){
		$modules = $this->config->get("modules", []);
		foreach($modules as $name => $main){
			$this->readModule($name, $main);
		}
	}

	public function start(){
		try{
			$this->displayTitle("SimpleFramework is starting...");
			@mkdir("modules");
			if(!file_exists($this->dataPath . "config.json")){
				$default = [
					"modules" => [
						"SimpleRefresher" => "refresher\\SimpleRefresher"
					]
				];
			}else{
				$default = [];
			}
			$this->config = new Config($this->dataPath . "config.json", Config::JSON, $default);
			$this->config->save();
			Logger::info(TextFormat::AQUA . "SimpleFramework " . TextFormat::LIGHT_PURPLE . self::PROG_VERSION);
			Logger::info(TextFormat::GOLD . "Licensed under GNU General Public License v3.0");
			Logger::info("Starting Console Daemon...");
			$this->console = new ConsoleReader();
			Logger::info("Starting Command Processor...");
			$this->commandProcessor = new CommandProcessor($this);
			$this->loadModules();
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