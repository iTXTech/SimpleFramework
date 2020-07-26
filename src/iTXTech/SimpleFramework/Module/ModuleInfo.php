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
	const ACCEPTABLE_MANIFEST_FILENAME = ["sf.php", "info.json"];

	private $name;
	private $main;
	private $api;
	private $version;
	private $description = null;
	private $authors = [];
	private $website = null;
	private $loadMethod;

	public function __construct(string $info, int $loadMethod){
		$this->loadMethod = $loadMethod;
		$info = json_decode($info, true);
		$this->name = preg_replace("[^A-Za-z0-9 _.-]", "", $info["name"]);
		if($this->name === ""){
			throw new \Exception("Invalid module name");
		}
		$this->name = str_replace(" ", "_", $this->name);
		$this->version = $info["version"];
		$this->main = $info["main"] ?? "";
		$this->api = $info["api"];

		$this->description = $info["description"] ?? "";
		$this->authors = [];
		$this->authors[] = $info["author"] ?? [];
		if(isset($info["authors"])){
			foreach($info["authors"] as $author){
				$this->authors[] = $author;
			}
		}
		$this->website = $info["website"] ?? null;
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
}
