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
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace iTXTech\SimpleFramework;

use iTXTech\SimpleFramework\Console\CommandProcessor;
use iTXTech\SimpleFramework\Console\ConsoleReader;
use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\Option\HelpFormatter;
use iTXTech\SimpleFramework\Console\Option\OptionBuilder;
use iTXTech\SimpleFramework\Console\Option\Options;
use iTXTech\SimpleFramework\Console\Option\Parser;
use iTXTech\SimpleFramework\Console\Terminal;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleFramework\Module\ModuleManager;
use iTXTech\SimpleFramework\Module\WraithSpireMDR;
use iTXTech\SimpleFramework\Scheduler\OnCompletionListener;
use iTXTech\SimpleFramework\Scheduler\Scheduler;
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

	/** @var Scheduler */
	private $scheduler;

	/** @var ModuleManager */
	private $moduleManager;

	private $shutdown = false;

	private $currentTick = 0;

	private $titleQueue = [];

	private $dataPath;
	private $modulePath;
	private $moduleDataPath;
	public $displayTitle = true;

	/** @var Options */
	private $options;

	public static $usleep = 50000;

	public function __construct(\ClassLoader $classLoader){
		if(self::$instance === null){
			self::$instance = $this;
		}
		$this->dataPath = \getcwd() . DIRECTORY_SEPARATOR;
		$this->modulePath = $this->dataPath . "modules" . DIRECTORY_SEPARATOR;
		$this->moduleDataPath = $this->dataPath . "data" . DIRECTORY_SEPARATOR;
		$this->classLoader = $classLoader;

		$this->options = new Options();
		$this->registerDefaultOptions();
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

	public function getAPILevel() : int{
		return self::API_LEVEL;
	}

	public static function getInstance() : ?Framework{
		return self::$instance;
	}

	public static function isStarted() : bool{
		return self::$instance !== null;
	}

	public function getScheduler() : ?Scheduler{
		return $this->scheduler;
	}

	public function getModuleManager() : ?ModuleManager{
		return $this->moduleManager;
	}

	private function registerDefaultOptions(){
		$this->options->addOption((new OptionBuilder("h"))->longOpt("help")
			->desc("Display this help message")->build());
		$this->options->addOption((new OptionBuilder("v"))->longOpt("version")
			->desc("Display version of SimpleFramework")->build());
		$this->options->addOption((new OptionBuilder("l"))->longOpt("disable-logger")
			->desc("Disable Logger output")->build());
		$this->options->addOption((new OptionBuilder("c"))->longOpt("disable-logger-class")
			->desc("Disable Logger Class detection")->build());
		$this->options->addOption((new OptionBuilder("p"))->longOpt("without-prefix")
			->desc("Do not print prefix when printing log")->build());
		$this->options->addOption((new OptionBuilder("a"))->longOpt("ansi")
			->desc("Enable or Disable ANSI")->hasArg()->argName("yes|no")->build());
		$this->options->addOption((new OptionBuilder("t"))->longOpt("title")
			->desc("Enable or Disable title display")->hasArg()->argName("yes|no")->build());

		//TODO
		$this->options->addOption((new OptionBuilder("l"))->longOpt("load-module")->hasArg()
			->desc("Load the specified module")->argName("path")->build());
		$this->options->addOption((new OptionBuilder("r"))->longOpt("cmd")->hasArg()
			->desc("Execute the specified command")->argName("command")->build());
	}

	private function processCommandLineOptions(array $argv) : bool{
		try{
			$cmd = (new Parser())->parse($this->options, $argv);
			if($cmd->hasOption("help")){
				$t = (new HelpFormatter())->generateHelp("sf", $this->options, true);
				echo $t;
				exit(0);
			}

			if($cmd->hasOption("version")){
				echo Framework::PROG_NAME . " " . Framework::PROG_VERSION .
					" [" . Framework::CODENAME . "]" . PHP_EOL .
					"Implementing API " . Framework::API_LEVEL . PHP_EOL;
				exit(0);
			}

			if($cmd->hasOption("disable-logger")){
				Logger::$disableOutput = true;
			}

			if($cmd->hasOption("disable-logger-class")){
				Logger::$disableClass = true;
			}

			if($cmd->hasOption("without-prefix")){
				Logger::$hasPrefix = false;
			}

			if($cmd->hasOption("ansi")){
				Terminal::$formattingCodes = Util::getCliOptBool($cmd->getOptionValue("ansi"));
				Terminal::init();
			}

			if($cmd->hasOption("title")){
				$this->displayTitle = false;
			}

		}catch(\Throwable $e){
			echo $e->getMessage() . PHP_EOL;
			$t = (new HelpFormatter())->generateHelp("sf", $this->options, true);
			echo $t;
			exit(1);
		}
		return false;
	}

	public function start(bool $useMainThreadTick = true, array $argv = []){
		try{
			if(!$this->processCommandLineOptions($argv)){
				//$this->displayTitle("SimpleFramework is starting...");
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

				if($this->moduleManager === null){
					$this->moduleManager = new ModuleManager($this->classLoader, $this->modulePath, $this->moduleDataPath);
				}

				if(!\iTXTech\SimpleFramework\SINGLE_THREAD){
					Logger::info("Starting ConsoleReader...");
					$this->console = new ConsoleReader();
				}

				Logger::info("Starting Command Processor...");
				if(!$this->commandProcessor instanceof CommandProcessor){
					$this->commandProcessor = new CommandProcessor();
					$this->commandProcessor->registerDefaultCommands();
				}

				Logger::info("Starting multi-threading scheduler...");
				$this->scheduler = new Scheduler($this->classLoader, $this, $this->config->get("async-workers", 2));

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

				if($useMainThreadTick){
					$this->tick();
				}
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
			$this->update();
			usleep(self::$usleep);
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
