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

namespace iTXTech\SimpleFramework\Util\Curl;

class Cookie{
	private $name;
	private $value;
	private $prop;

	public function __construct(string $payload){
		$parts = explode("; ", $payload);
		list($this->name, $this->value) = explode("=", $parts[0]);
		array_shift($parts);
		foreach($parts as $part){
			$p = explode("=", $part);
			$this->prop[$p[0]] = $p[1] ?? "";
		}
	}

	public function getName(){
		return $this->name;
	}

	public function getValue(){
		return $this->value;
	}

	public function hasProperty(string $name){
		return isset($this->prop[$name]);
	}

	public function getProperty(string $name) : ?string{
		return $this->prop[$name] ?? null;
	}

	public function __toString(){
		$buffer = $this->name . "=" . $this->value . "; ";
		foreach($this->prop as $k => $v){
			if($v === ""){
				$buffer .= $k . "; ";
			}else{
				$buffer .= $k . "=$v; ";
			}
		}
		return substr($buffer, 0, strlen($buffer) - 2);
	}
}
