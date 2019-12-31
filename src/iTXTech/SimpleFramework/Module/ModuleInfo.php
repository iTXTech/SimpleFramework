<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2020 iTX Technologies
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

namespace iTXTech\SimpleFramework\Module;

class ModuleInfo{
	const ACCEPTABLE_MANIFEST_FILENAME = ["sf.json", "info.json"];

	const LOAD_METHOD_PACKAGE = 0;
	const LOAD_METHOD_SOURCE = 1;

	private $name;
	private $main;
	private $api;
	private $version;
	private $description = null;
	private $authors = [];
	private $website = null;
	private $loadMethod;
	private $dependencies = [];
	private $extensions = [];
	private $hotPatch = [];
	private $stub;
	private $sfloader;
	private $packer;
	private $composer;

	public function __construct(string $info, int $loadMethod){
		$this->loadMethod = $loadMethod;
		$info = json_decode($info, true);
		$this->name = preg_replace("[^A-Za-z0-9 _.-]", "", $info["name"]);
		if($this->name === ""){
			throw new \Exception("Invalid module name");
		}
		$this->name = str_replace(" ", "_", $this->name);
		$this->version = $info["version"];
		$this->main = $info["main"];
		$this->api = $info["api"];
		$this->hotPatch = $info["hotPatch"] ?? [];

		$this->description = $info["description"] ?? "";
		$this->authors = [];
		$this->authors[] = $info["author"] ?? [];
		if(isset($info["authors"])){
			foreach($info["authors"] as $author){
				$this->authors[] = $author;
			}
		}
		$this->website = $info["website"] ?? null;

		$this->dependencies = $info["dependencies"] ?? [];
		if($this->dependencies === []){
			$this->dependencies = $info["dependency"] ?? [];//backward compatibility
		}

		$this->extensions = $info["extensions"] ?? [];
		$this->stub = $info["stub"] ?? null;
		$this->sfloader = $info["sfloader"] ?? false;
		$this->packer = $info["packer"] ?? null;
		$this->composer = $info["composer"] ?? false;
	}

	public function composer() : bool{
		return $this->composer;
	}

	public function getDependencies() : array{
		return $this->dependencies;
	}

	public function getLoadMethod() : int{
		return $this->loadMethod;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getVersion() : string{
		return $this->version;
	}

	public function getDescription() : string{
		return $this->description;
	}

	public function getAuthors() : array{
		return $this->authors;
	}

	public function getApi() : int{
		return $this->api;
	}

	public function getMain() : ?string{
		return $this->main;
	}

	public function getWebsite() : string{
		return $this->website;
	}

	public function getExtensions() : array{
		return $this->extensions;
	}

	public function getHotPatch() : array{
		return $this->hotPatch;
	}

	public function getStub() : ?string{
		return $this->stub;
	}

	public function bundleSfLoader() : bool{
		return $this->sfloader;
	}

	public function getPacker() : ?string{
		return $this->packer;
	}
}
