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

namespace iTXTech\SimpleFramework\Module;

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleFramework\Framework;
use iTXTech\SimpleFramework\Util\StringUtil;
use iTXTech\SimpleFramework\Util\Util;

abstract class Module{
	/** @var Framework */
	protected $framework;

	/** @var ModuleInfo */
	private $info;

	/** @var ModuleManager */
	private $manager;

	private $file;
	private $dataFolder;
	private $loaded = false;

	public final function __construct(ModuleManager $manager, ModuleInfo $info, string $file){
		$this->file = $file . DIRECTORY_SEPARATOR;
		$this->manager = $manager;
		$this->framework = Framework::getInstance();
		$this->info = $info;
		$this->dataFolder = $manager->getModuleDataPath() . $info->getName() . DIRECTORY_SEPARATOR;
	}

	public function getDataFolder() : string{
		return $this->dataFolder;
	}

	public final function setLoaded(bool $loaded){
		$this->loaded = $loaded;
	}

	public final function getFramework() : Framework{
		return $this->framework;
	}

	public function preLoad() : bool{
		if($this->info->getApi() > Framework::API_LEVEL){
			Logger::error("Module requires API: " . $this->info->getApi() . " Current API: " . Framework::API_LEVEL);
			return false;
		}
		if($this->checkExtensions()){
			return (($resolver = $this->manager->getModuleDependencyResolver()) instanceof ModuleDependencyResolver) ?
				$resolver->resolveDependencies($this) : $this->checkDependencies();
		}
		return false;
	}

	protected function checkDependencies() : bool{
		$dependencies = $this->info->getDependencies();
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
				Logger::error("Module " . '"' . $this->getName() . '"' . " requires module " . '"' . $name . '"' .
					" version " . ($dependency["version"] ?? "Unspecified"));
				if(!($dependency["optional"] ?? false)){
					return false;
				}
			}
		}
		return true;
	}

	protected function checkExtensions() : bool{
		$extensions = $this->info->getExtensions();
		foreach($extensions as $extension){
			$error = true;
			if(extension_loaded($extension["name"])){
				if(isset($extension["version"])){
					$extVer = (new \ReflectionExtension($extension["name"]))->getVersion();
					$error = Util::compareVersion($extension["version"], $extVer);
				}else{
					$error = false;
				}
			}
			if($error){
				Logger::error("Module " . '"' . $this->getName() . '"' . " requires extension " . '"' . $extension["name"] . '"' .
					" version " . ($extension["version"] ?? "Unspecified"));
				return false;
			}
		}
		return true;
	}

	public abstract function load();

	public abstract function unload();

	public final function isLoaded() : bool{
		return $this->loaded;
	}

	public function doTick(int $currentTick){
	}

	public final function getInfo() : ModuleInfo{
		return $this->info;
	}

	public final function getName() : string{
		return $this->info->getName();
	}

	public function getResource(string $file){
		$file = rtrim(str_replace("\\", "/", $file), "/");
		if(file_exists($this->file . "resources/" . $file)){
			return fopen($this->file . "resources/" . $file, "rb");
		}

		return null;
	}

	public function getResourceAsText(string $file){
		$file = rtrim(str_replace("\\", "/", $file), "/");
		if(file_exists($this->file . "resources/" . $file)){
			return file_get_contents($this->file . "resources/" . $file);
		}

		return null;
	}

	/**
	 * @param string $filename
	 * @param bool   $replace
	 *
	 * @return bool
	 */
	public function saveResource($filename, $replace = false){
		if(trim($filename) === ""){
			return false;
		}

		if(($resource = $this->getResource($filename)) === null){
			return false;
		}

		$out = $this->dataFolder . $filename;
		if(!file_exists(dirname($out))){
			mkdir(dirname($out), 0755, true);
		}

		if(file_exists($out) and $replace !== true){
			return false;
		}

		$ret = stream_copy_to_stream($resource, $fp = fopen($out, "wb")) > 0;
		fclose($fp);
		fclose($resource);
		return $ret;
	}

	/**
	 * Returns all the resources packaged with the plugin
	 *
	 * @return string[]
	 */
	public function getResources(){
		$resources = [];
		if(is_dir($this->file . "resources/")){
			foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->file . "resources/")) as $resource){
				$resources[] = $resource;
			}
		}

		return $resources;
	}

	public function getFile() : string{
		return $this->file;
	}

	protected function onHotPatch() : bool{
		return true;
	}

	public function doHotPatch(){
		if($this->onHotPatch()){
			$indexes = $this->getInfo()->getHotPatch();
			foreach($indexes as $index){
				$thread = new HotPatchThread($index["class"], $index["method"], $this->manager->getClassLoader());
				$thread->start(PTHREADS_INHERIT_NONE);
				while($thread->getCode() == null) ;//wait until thread is finished
				$codes = explode(PHP_EOL, $thread->getCode());
				$args = StringUtil::between($codes[0], "(", ")");
				unset($codes[count($codes) - 1]);
				unset($codes[count($codes) - 1]);
				unset($codes[0]);
				$code = implode(PHP_EOL, $codes);
				\runkit7_method_redefine($index["class"], $index["method"], $args, $code);
			}
		}
	}

	public function pack(string $path, string $filename = null, bool $includeGitInfo = true, bool $compress = true, bool $log = false) : bool{
		$info = $this->getInfo();

		if(!($info->getLoadMethod() == ModuleInfo::LOAD_METHOD_SOURCE)){
			if($log){
				Logger::error(TextFormat::RED . "Module " . $info->getName() . " is not in folder structure.");
			}
			return false;
		}

		@mkdir($path);
		$pharPath = $path . ($filename ?? $info->getName() . "_v" . $info->getVersion() . ".phar");
		if(file_exists($pharPath)){
			if($log){
				Logger::debug("Phar module already exists, overwriting...");
			}
			@\Phar::unlinkArchive($pharPath);
		}
		$git = "Unknown";
		if($includeGitInfo){
			$git = Util::getLatestGitCommitId($this->getFile()) ?? "Unknown";
		}
		$phar = new \Phar($pharPath);
		$phar->setMetadata([
			"name" => $info->getName(),
			"version" => $info->getVersion(),
			"main" => $info->getMain(),
			"api" => $info->getApi(),
			"description" => $info->getDescription(),
			"authors" => $info->getAuthors(),
			"generator" => Framework::PROG_NAME . " " . Framework::PROG_VERSION,
			"gitCommitId" => $git,
			"creationDate" => time()
		]);
		$phar->setStub('<?php echo "' . Framework::PROG_NAME . ' module ' . $info->getName() . ' v' . $info->getVersion() . '\n----------------\n";if(extension_loaded("phar")){$phar = new \Phar(__FILE__);foreach($phar->getMetadata() as $key => $value){echo ucfirst($key).": ".(is_array($value) ? implode(", ", $value):$value)."\n";}} __HALT_COMPILER();');
		$phar->setSignatureAlgorithm(\Phar::SHA1);
		$filePath = rtrim(str_replace("\\", "/", $this->getFile()), "/") . "/";
		$phar->startBuffering();
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filePath)) as $file){
			$path = ltrim(str_replace(["\\", $filePath], ["/", ""], $file), "/");
			if($path{0} === "." or strpos($path, "/.") !== false){
				continue;
			}
			$phar->addFile($file, $path);
			if($log){
				Logger::info("Adding $path");
			}
		}

		foreach($phar as $file => $finfo){
			/** @var \PharFileInfo $finfo */
			if($finfo->getSize() > (1024 * 512)){
				$finfo->compress(\Phar::GZ);
			}
		}
		if($compress){
			$phar->compressFiles(\Phar::GZ);
		}
		$phar->stopBuffering();
		if($log){
			Logger::info("Phar module " . $info->getName() . " v" . $info->getVersion() . " has been created in " . $pharPath);
		}
		return true;
	}

	public function unpack(string $path, string $folderName = null, bool $log = false) : bool{
		$info = $this->getInfo();

		if(!($info->getLoadMethod() == ModuleInfo::LOAD_METHOD_PACKAGE)){
			if($log){
				Logger::error(TextFormat::RED . "Module " . $info->getName() . " is not in Phar structure.");
			}
			return false;
		}

		$folderPath = $path . ($folderName ?? $info->getName() . "_v" . $info->getVersion() . DIRECTORY_SEPARATOR);
		if(file_exists($folderPath)){
			if($log){
				Logger::debug("Module files already exist, overwriting...");
			}
		}else{
			@mkdir($folderPath);
		}

		$pharPath = str_replace("\\", "/", rtrim($this->getFile(), "\\/"));

		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pharPath)) as $fInfo){
			$path = $fInfo->getPathname();
			@mkdir(dirname($folderPath . str_replace($pharPath, "", $path)), 0755, true);
			file_put_contents($folderPath . str_replace($pharPath, "", $path), file_get_contents($path));
		}
		if($log){
			Logger::info("Module " . $info->getName() . " v" . $info->getVersion() . " has been unpacked into " . $folderPath);
		}
		return true;
	}
}
