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

namespace sf\module;

class ModuleInfo{
	private $name;
	private $main;
	private $api;
	private $version;
	private $description = null;
	private $authors = [];

	public function __construct(string $info){
		$info = json_decode($info, true);
		$this->name = preg_replace("[^A-Za-z0-9 _.-]", "", $info["name"]);
		if($this->name === ""){
			throw new \Exception("Invalid plugin name");
		}
		$this->name = str_replace(" ", "_", $this->name);
		$this->version = $info["version"];
		$this->main = $info["main"];
		$this->api = $info["api"];

		if(stripos($this->main, "sf\\") === 0){
			throw new \Exception("Invalid plugin main class.");
		}

		if(isset($info["description"])){
			$this->description = $info["description"];
		}
		$this->authors = [];
		if(isset($info["author"])){
			$this->authors[] = $info["author"];
		}
		if(isset($info["authors"])){
			foreach($info["authors"] as $author){
				$this->authors[] = $author;
			}
		}
	}

	public final function getName() : string{
		return $this->name;
	}

	public final function getVersion() : string{
		return $this->version;
	}

	public final function getDescription() : string{
		return $this->description;
	}

	public final function getAuthors() : array{
		return $this->authors;
	}

	public final function getAPI() : int{
		return $this->api;
	}

	public final function getMain(){
		return $this->main;
	}
}