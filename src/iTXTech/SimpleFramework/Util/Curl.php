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

namespace iTXTech\SimpleFramework\Util;

//This class is created for backward compatibility
//TODO: Deprecate in 2.3
class Curl extends \iTXTech\SimpleFramework\Util\Curl\Curl{
	protected $content;

	public function returnHeader(bool $bool){
		curl_setopt($this->curl, CURLOPT_HEADER, $bool ? 1 : 0);
		return $this;
	}

	public function returnBody(bool $bool){
		curl_setopt($this->curl, CURLOPT_NOBODY, $bool ? 0 : 1);
		return $this;
	}

	public function setCookie(array $cookies){
		$payload = "";
		foreach($cookies as $key => $cookie){
			$payload .= "$key=$cookie; ";
		}
		$payload = substr($payload, 0, strlen($payload) - 2);
		curl_setopt($this->curl, CURLOPT_COOKIE, $payload);
		return $this;
	}

	public function setUA(string $ua){
		parent::setUserAgent($ua);
	}

	public function getContent(){
		return $this->content;
	}

	public function exec(){
		$this->content = curl_exec($this->curl);
		$this->reload();
		return $this->content;
	}

	public function getCookie(){
		preg_match_all('/Set-Cookie: (.*);/iU', $this->content, $cookies);
		$payload = [];
		foreach($cookies[1] as $cookie){
			$key = explode('=', $cookie);
			if(isset($payload[$key[0]]) and $payload[$key[0]] !== ''){
				continue;
			}
			$payload[$key[0]] = $key[1];
		}
		return $payload;
	}
}
