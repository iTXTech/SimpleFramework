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
use iTXTech\SimpleFramework\Util\FrameworkProperties;
use iTXTech\SimpleFramework\Util\StringUtil;
use iTXTech\SimpleFramework\Util\Util;

class Framework implements OnCompletionListener{
	public const PROG_NAME = "SimpleFramework";
	public const PROG_VERSION = "2.1.0";
	public const API_LEVEL = 6;
	public const CODENAME = "Navi";

	/** @var Framework */
	private static $instance = null;

	private static $tickInterval = 50000;

	//Objects
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

		$this->options->addOption((new OptionBuilder("a"))->longOpt("ansi")
			->desc("Enable or disable ANSI")->hasArg()->argName("yes|no")->build());
		$this->options->addOption((new OptionBuilder("b"))->longOpt("config")
			->desc("Overwrite specified config property")->hasArg()->argName("prop")->build());
		//c, d

		$this->options->addOption((new OptionBuilder("e"))->longOpt("disable-logger")
			->desc("Disable Logger output")->build());
		$this->options->addOption((new OptionBuilder("f"))->longOpt("disable-logger-class")
			->desc("Disable Logger Class detection")->build());
		$this->options->addOption((new OptionBuilder("g"))->longOpt("without-prefix")
			->desc("Do not print prefix when printing log")->build());
		//i, j, k

		$this->options->addOption((new OptionBuilder("l"))->longOpt("data-path")
			->desc("Specify SimpleFramework data path")->hasArg()->argName("path")->build());
		$this->options->addOption((new OptionBuilder("m"))->longOpt("module-path")
			->desc("Specify SimpleFramework module path")->hasArg()->argName("path")->build());
		$this->options->addOption((new OptionBuilder("n"))->longOpt("module-data-path")
			->desc("Specify SimpleFramework module data path")->hasArg()->argName("path")->build());
		$this->options->addOption((new OptionBuilder("o"))->longOpt("config-path")
			->desc("Specify SimpleFramework config file")->hasArg()->argName("path")->build());
		//p, q

		$this->options->addOption((new OptionBuilder("r"))->longOpt("load-module")->hasArg()
			->desc("Load the specified module")->argName("path")->build());
		$this->options->addOption((new OptionBuilder("s"))->longOpt("run-command")->hasArg()
			->desc("Execute the specified command")->argName("command")->build());
		//t, u, w, x, y, z
	}

	private function processCommandLineOptions(array $argv){
		try{
			$cmd = (new Parser())->parse($this->options, $argv);
			if($cmd->hasOption("help")){
				$t = (new HelpFormatter())->generateHelp("sf", $this->options);
				echo $t;
				exit(0);
			}
			if($cmd->hasOption("version")){
				if(($phar = \Phar::running(true)) !== ""){
					$phar = new \Phar($phar);
					$built = date("r", $phar->getMetadata()["creationDate"]) . " (Phar)";
					$git = $phar->getMetadata()["gitCommitId"];
				}else{
					$built = date("r") . " (Source)";
					$git = Util::getLatestGitCommitId(\iTXTech\SimpleFramework\PATH) ?? "Unknown";
				}

				Util::println(Framework::PROG_NAME . " " . Framework::PROG_VERSION .
					" \"" . Framework::CODENAME . "\" (API " . Framework::API_LEVEL . ")");
				Util::println("Built: " . $built);
				Util::println("Git CID: " . $git);
				Util::println("Copyright (C) 2016-2019 iTX Technologies");
				Util::println(str_repeat("-", 30));
				Util::println("OS => " . PHP_OS_FAMILY . " " . php_uname("r"));
				Util::println("PHP => " . PHP_VERSION);
				foreach(["curl", "Phar", "pthreads", "yaml", "swoole"] as $ext){
					Util::println(Util::generateExtensionInfo($ext));
				}
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
			if($cmd->hasOption("load-module")){
				foreach($cmd->getOptionValues("load-module") as $value){
					$this->properties->additionalModules[] = $value;
				}
			}
			if($cmd->hasOption("run-command")){
				foreach($cmd->getOptionValues("run-command") as $value){
					$this->properties->commands[] = $value;
				}
			}
			if($cmd->hasOption("data-path")){
				$this->properties->dataPath = $cmd->getOptionValue("data-path");
				$this->properties->generatePath();
			}
			if($cmd->hasOption("module-path")){
				$this->properties->modulePath = $cmd->getOptionValue("module-path");
			}
			if($cmd->hasOption("module-data-path")){
				$this->properties->moduleDataPath = $cmd->getOptionValue("module-data-path");
			}
			if($cmd->hasOption("config")){
				foreach($cmd->getOptionValues("config") as $value){
					list($k, $v) = explode("=", $value, 2);
					if(strtolower($v) == "false"){
						$v = false;
					}elseif(strtolower($v) == "true"){
						$v = true;
					}
					if(StringUtil::contains($k, ".")){
						list($k1, $k2) = explode(".", $k);
						$this->properties->config[$k1][$k2] = $v;
					}else{
						$this->properties->config[$k] = $v;
					}
				}
			}
		}catch(\Throwable $e){
			Util::println($e->getMessage());
			$t = (new HelpFormatter())->generateHelp("sf", $this->options);
			echo $t;
			exit(1);
		}
	}

	public function start(bool $useMainThreadTick = true, array $argv = []){
		try{
			$this->processCommandLineOptions($argv);
			$this->properties->mkdirDirs();

			set_exception_handler("\\iTXTech\\SimpleFramework\\Console\\Logger::logException");

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

			//TODO: preload plugins before initialize
			if($this->moduleManager === null){
				$this->moduleManager = new ModuleManager($this->classLoader,
					$this->properties->modulePath, $this->properties->moduleDataPath);
			}

			if(!\iTXTech\SimpleFramework\SINGLE_THREAD){
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

			if(($mdr = $this->moduleManager->getModuleDependencyResolver()) instanceof WraithSpireMDR){
				/** @var WraithSpireMDR $mdr */
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

	private function tick(){
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
		Logger::info("Stopping SimpleFramework...");
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
