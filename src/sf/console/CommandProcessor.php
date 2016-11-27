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

namespace sf\console;

use sf\module\Module;
use sf\module\ModuleInfo;
use sf\Framework;

class CommandProcessor{
	/** @var Command[] */
	private $registeredCommands;

	public function __construct(){
		$this->registerCommands();
	}

	public function getCommands(){
		return $this->registeredCommands;
	}

	public function registerCommands(){
		$this->register(new HelpCommand(), "help");
		$this->register(new VersionCommand(), "version");
		$this->register(new StopCommand(), "stop");
		$this->register(new ModulesCommand(), "modules");
		$this->register(new ClearCommand(), "clear");

		$this->register(new PackModule(), "pm");
		$this->register(new PackSF(), "psf");
		$this->register(new UnpackModule(), "um");
	}

	public function register(Command $command, string $name){
		$this->registeredCommands[$name] = $command;
	}

	public function unregister(string $name) : bool{
		if(isset($this->registeredCommands[strtolower($name)])){
			unset($this->registeredCommands[strtolower($name)]);
			return true;
		}
		return false;
	}

	public function dispatchCommand(string $commandLine){
		$args = explode(" ", $commandLine);
		$command = strtolower(array_shift($args));
		if(isset($this->registeredCommands[$command])){
			if(!$this->registeredCommands[$command]->execute($command, $args)){
				Logger::info(TextFormat::GOLD . "Usage: " . TextFormat::WHITE . $this->registeredCommands[$command]->getUsage());
			}
		}else{
			Logger::info(TextFormat::RED . "Command '$command' not found. Type 'help' for help");
		}
	}
}

//Commands

interface Command{
	public function getName() : string;

	public function getUsage() : string;

	public function getDescription() : string;

	public function execute(string $command, array $args) : bool;
}

class HelpCommand implements Command{
	public function getName() : string{
		return "help";
	}

	public function getUsage() : string{
		return "help (command)";
	}

	public function getDescription() : string{
		return "Gets the help of commands.";
	}

	public function execute(string $command, array $args) : bool{
		$commands = Framework::getInstance()->getCommandProcessor()->getCommands();
		if(count($args) > 0){
			$command = strtolower($args[0]);
			if(isset($commands[$command])){
				Logger::info(TextFormat::YELLOW . "---------- " . TextFormat::WHITE . "Help: " . $command . TextFormat::YELLOW . " ----------");
				Logger::info(TextFormat::GOLD . "Usage: " . TextFormat::WHITE . $commands[$command]->getUsage());
				Logger::info(TextFormat::GOLD . "Description: " . TextFormat::WHITE . $commands[$command]->getDescription());
			}else{
				Logger::info(TextFormat::RED . "Not found help for $command");
			}
		}else{
			ksort($commands, SORT_NATURAL | SORT_FLAG_CASE);
			Logger::info(TextFormat::YELLOW . "---------- " . TextFormat::WHITE . "Registered Commands: " . count($commands) . TextFormat::YELLOW . " ----------");
			foreach($commands as $command){
				Logger::info(TextFormat::GREEN . $command->getName() . ": " . TextFormat::WHITE . $command->getDescription());
			}
		}
		return true;
	}
}

class VersionCommand implements Command{
	public function getName() : string{
		return "version";
	}

	public function getUsage() : string{
		return "version (moduleName)";
	}

	public function getDescription() : string{
		return "Gets the version of SimpleFramework or the version of a module.";
	}

	public function execute(string $command, array $args) : bool{
		if(count($args) > 0){
			$module = $args[0];
			$modules = Framework::getInstance()->getModules();
			if(isset($modules[$module])){
				$module = $modules[$module];
				Logger::info(TextFormat::YELLOW . "--------- " . TextFormat::WHITE . "Version: " . $module->getInfo()->getName() . TextFormat::YELLOW . " ---------");
				Logger::info(TextFormat::GOLD . "Version: " . TextFormat::WHITE . $module->getInfo()->getVersion());
				Logger::info(TextFormat::GOLD . "Description: " . TextFormat::WHITE . $module->getInfo()->getDescription());
			}else{
				Logger::error(TextFormat::RED . "Module " . $module . " is not installed.");
			}
		}else{
			Logger::info(TextFormat::AQUA . "SimpleFramework " . TextFormat::LIGHT_PURPLE . Framework::PROG_VERSION . TextFormat::WHITE . " Implementing API level: " . TextFormat::GREEN . Framework::API_LEVEL);
		}
		return true;
	}
}

class StopCommand implements Command{
	public function getName() : string{
		return "stop";
	}

	public function getUsage() : string{
		return "stop";
	}

	public function getDescription() : string{
		return "Stop the framework and all the modules.";
	}

	public function execute(string $command, array $args) : bool{
		Framework::getInstance()->shutdown();
		return true;
	}
}

class ModulesCommand implements Command{
	public function getName() : string{
		return "modules";
	}

	public function getUsage() : string{
		return "modules <list|load|unload|read> <ModuleName or File/Folder Name>";
	}

	public function getDescription() : string{
		return "The manage command for Modules.";
	}

	public function execute(string $command, array $args) : bool{
		if(count($args) < 1){
			return false;
		}
		$modules = Framework::getInstance()->getModules();
		switch(strtolower($args[0])){
			case "list":
				$message = "Modules (" . count($modules) . "): ";
				$msg = [];
				foreach($modules as $module){
					$msg[] = ($module->isLoaded() ? TextFormat::GREEN : TextFormat::RED) . $module->getInfo()->getName() . " v" . $module->getInfo()->getVersion();
				}
				$message .= implode(TextFormat::WHITE . ", ", $msg);
				Logger::info($message);
				return true;
			case "load":
				if(count($args) > 1){
					$name = $args[1];
					if(isset($modules[$name])){
						$module = $modules[$name];
						Framework::getInstance()->loadModule($module);
					}else{
						Logger::info(TextFormat::RED . "Module $name is not installed.");
					}
					return true;
				}else{
					return false;
				}
			case "unload":
				if(count($args) > 1){
					$name = $args[1];
					if(isset($modules[$name])){
						$module = $modules[$name];
						Framework::getInstance()->unloadModule($module);
					}else{
						Logger::info(TextFormat::RED . "Module $name is not installed.");
					}
					return true;
				}else{
					return false;
				}
			case "read":
				if(count($args) > 1){
					$file = $args[1];
					if(!file_exists(Framework::getInstance()->getModulePath() . $file)){
						Logger::info(TextFormat::RED . "File not found.");
						return true;
					}
					Framework::getInstance()->tryLoadModule(Framework::getInstance()->getModulePath() . $file);
					return true;
				}else{
					return false;
				}
			default:
				return false;
		}
	}
}

class ClearCommand implements Command{
	public function getName() : string{
		return "clear";
	}

	public function getUsage() : string{
		return "clear";
	}

	public function getDescription() : string{
		return "Clears the screen.";
	}

	public function execute(string $command, array $args) : bool{
		echo "\x1bc";
		return true;
	}
}

class PackModule implements Command{
	public function getName() : string{
		return "pm";
	}

	public function getUsage() : string{
		return "pm <Module Name> (no-gz) (no-echo)";
	}

	public function getDescription() : string{
		return "Pack a source module into Phar archive.";
	}

	public function execute(string $command, array $args) : bool{
		$moduleName = trim(str_replace(["no-gz", "no-echo"], "", implode(" ", $args)));

		if($moduleName === "" or !(($module = Framework::getInstance()->getModule($moduleName)) instanceof Module)){
			Logger::info(TextFormat::RED . "Invalid module name, check the name case.");
			return true;
		}
		$info = $module->getInfo();

		if(!($info->getLoadMethod() == ModuleInfo::LOAD_METHOD_SOURCE)){
			Logger::info(TextFormat::RED . "Module " . $info->getName() . " is not in folder structure.");
			return true;
		}

		$outputDir = Framework::getInstance()->getModuleDataPath() . "module" . DIRECTORY_SEPARATOR;
		@mkdir($outputDir);
		$pharPath = $outputDir . $info->getName() . "_v" . $info->getVersion() . ".phar";
		if(file_exists($pharPath)){
			Logger::info("Phar module already exists, overwriting...");
			@\Phar::unlinkArchive($pharPath);
		}
		$phar = new \Phar($pharPath);
		$phar->setMetadata([
			"name" => $info->getName(),
			"version" => $info->getVersion(),
			"main" => $info->getMain(),
			"api" => $info->getAPILevel(),
			"description" => $info->getDescription(),
			"authors" => $info->getAuthors(),
			"creationDate" => time()
		]);
		$phar->setStub('<?php echo "' . Framework::PROG_NAME . ' module ' . $info->getName() . ' v' . $info->getVersion() . '\nThis file has been generated using PackModule Command at ' . date("r") . '\n----------------\n";if(extension_loaded("phar")){$phar = new \Phar(__FILE__);foreach($phar->getMetadata() as $key => $value){echo ucfirst($key).": ".(is_array($value) ? implode(", ", $value):$value)."\n";}} __HALT_COMPILER();');
		$phar->setSignatureAlgorithm(\Phar::SHA1);
		$reflection = new \ReflectionClass("sf\\module\\Module");
		$file = $reflection->getProperty("file");
		$file->setAccessible(true);
		$filePath = rtrim(str_replace("\\", "/", $file->getValue($module)), "/") . "/";
		$phar->startBuffering();
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filePath)) as $file){
			$path = ltrim(str_replace(["\\", $filePath], ["/", ""], $file), "/");
			if($path{0} === "." or strpos($path, "/.") !== false){
				continue;
			}
			$phar->addFile($file, $path);
			if(!in_array("no-echo", $args)){
				Logger::info("Adding $path");
			}
		}

		foreach($phar as $file => $finfo){
			/** @var \PharFileInfo $finfo */
			if($finfo->getSize() > (1024 * 512)){
				$finfo->compress(\Phar::GZ);
			}
		}
		if(!in_array("no-gz", $args)){
			$phar->compressFiles(\Phar::GZ);
		}
		$phar->stopBuffering();
		Logger::info("Phar module " . $info->getName() . " v" . $info->getVersion() . " has been created in " . $pharPath);
		return true;
	}
}

class PackSF implements Command{
	public function getName() : string{
		return "psf";
	}

	public function getUsage() : string{
		return "psf  (no-gz) (no-echo)";
	}

	public function getDescription() : string{
		return "Pack the framework into Phar archive.";
	}

	public function execute(string $command, array $args) : bool{
		$outputDir = Framework::getInstance()->getModuleDataPath() . "module" . DIRECTORY_SEPARATOR;
		@mkdir($outputDir);
		$framework = Framework::getInstance();
		$pharPath = $outputDir . $framework->getName() . "_" . $framework->getVersion() . ".phar";
		if(file_exists($pharPath)){
			Logger::info("Phar file already exists, overwriting...");
			@\Phar::unlinkArchive($pharPath);
		}
		$phar = new \Phar($pharPath);
		$phar->setMetadata([
			"name" => $framework->getName(),
			"version" => $framework->getVersion(),
			"api" => $framework->getAPILevel(),
			"creationDate" => time()
		]);
		$phar->setStub('<?php define("sf\\\\PATH", "phar://". __FILE__ ."/"); require_once("phar://". __FILE__ ."/src/sf/SimpleFramework.php");  __HALT_COMPILER();');
		$phar->setSignatureAlgorithm(\Phar::SHA1);
		$phar->startBuffering();

		$filePath = substr(\sf\PATH, 0, 7) === "phar://" ? \sf\PATH : realpath(\sf\PATH) . "/";
		$filePath = rtrim(str_replace("\\", "/", $filePath), "/") . "/";
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filePath . "src")) as $file){
			$path = ltrim(str_replace(["\\", $filePath], ["/", ""], $file), "/");
			if($path{0} === "." or strpos($path, "/.") !== false or substr($path, 0, 4) !== "src/"){
				continue;
			}
			$phar->addFile($file, $path);
			if(!in_array("no-echo", $args)){
				Logger::info("Adding $path");
			}
		}
		foreach($phar as $file => $finfo){
			/** @var \PharFileInfo $finfo */
			if($finfo->getSize() > (1024 * 512)){
				$finfo->compress(\Phar::GZ);
			}
		}
		if(!in_array("no-gz", $args)){
			$phar->compressFiles(\Phar::GZ);
		}
		$phar->stopBuffering();

		Logger::info($framework->getName() . " " . $framework->getVersion() . " Phar archive has been created in " . $pharPath);

		return true;
	}
}

class UnpackModule implements Command{
	public function getName() : string{
		return "um";
	}

	public function getUsage() : string{
		return "um <Module Name>";
	}

	public function getDescription() : string{
		return "Unpack a module into source code.";
	}

	public function execute(string $command, array $args) : bool{
		$moduleName = trim(implode(" ", $args));
		if($moduleName === "" or !(($module = Framework::getInstance()->getModule($moduleName)) instanceof Module)){
			Logger::info(TextFormat::RED . "Invalid module name, check the name case.");
			return true;
		}
		$info = $module->getInfo();

		if(!($info->getLoadMethod() == ModuleInfo::LOAD_METHOD_PACKAGE)){
			Logger::info(TextFormat::RED . "Module " . $info->getName() . " is not in Phar structure.");
			return true;
		}

		$outputDir = Framework::getInstance()->getModuleDataPath() . "module" . DIRECTORY_SEPARATOR;
		$folderPath = $outputDir . $info->getName() . "_v" . $info->getVersion() . DIRECTORY_SEPARATOR;
		if(file_exists($folderPath)){
			Logger::info("Module files already exist, overwriting...");
		}else{
			@mkdir($folderPath);
		}

		$reflection = new \ReflectionClass("sf\\module\\Module");
		$file = $reflection->getProperty("file");
		$file->setAccessible(true);
		$pharPath = str_replace("\\", "/", rtrim($file->getValue($module), "\\/"));

		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pharPath)) as $fInfo){
			$path = $fInfo->getPathname();
			@mkdir(dirname($folderPath . str_replace($pharPath, "", $path)), 0755, true);
			file_put_contents($folderPath . str_replace($pharPath, "", $path), file_get_contents($path));
		}
		Logger::info("Module " . $info->getName() . " v" . $info->getVersion() . " has been unpacked into " . $folderPath);
		return true;
	}
}