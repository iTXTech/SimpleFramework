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

use iTXTech\SimpleFramework\Util\Curl\Preprocessor;

/**
 * @deprecated 2.3.0
 */
class Curl extends \iTXTech\SimpleFramework\Util\Curl\Curl{
	protected $content;

	public function returnHeader(bool $bool){
		$this->setOpt(CURLOPT_HEADER, $bool ? 1 : 0);
		return $this;
	}

	public function returnBody(bool $bool){
		$this->setOpt(CURLOPT_NOBODY, $bool ? 0 : 1);
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
		return parent::setUserAgent($ua);
	}

	public function setUrl(string $url){
		$this->url = $url;
		curl_setopt($this->curl, CURLOPT_URL, $url);
		return $this;
	}

	public function setHeader($arr, string $v = ""){
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, $arr);
		return $this;
	}

	public function getContent(){
		return $this->content;
	}

	public function exec(){
		curl_setopt_array($this->curl, $this->curlOpts);
		curl_setopt($this->curl, CURLOPT_URL, $this->url);
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

	public function uploadFile(array $assoc = [], array $files = [],
							   string $fileType = "application/octet-stream",
							   array $extraHeaders = []){
		$body = [];
		// invalid characters for "name" and "filename"
		$disallow = ["\0", "\"", "\r", "\n"];

		// build normal parameters
		foreach($assoc as $k => $v){
			$k = str_replace($disallow, "_", $k);
			$body[] = implode("\r\n", [
				"Content-Disposition: form-data; name=\"{$k}\"",
				"",
				filter_var($v),
			]);
		}

		foreach($files as $k => $v){
			switch(true){
				case false === $v = realpath(filter_var($v)):
				case !is_file($v):
				case !is_readable($v):
					continue 2;
			}
			$data = file_get_contents($v);
			$v = explode(DIRECTORY_SEPARATOR, $v);
			$v = end($v);
			$k = str_replace($disallow, "_", $k);
			$v = str_replace($disallow, "_", $v);
			$body[] = implode("\r\n", [
				"Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v}\"",
				"Content-Type: $fileType",
				"",
				$data,
			]);
		}

		// generate safe boundary
		do{
			$boundary = "---------------------" . md5(mt_rand() . microtime());
		}while(preg_grep("/{$boundary}/", $body));

		// add boundary for each parameters
		array_walk($body, function (&$part) use ($boundary){
			$part = "--{$boundary}\r\n{$part}";
		});

		// add final boundary
		$body[] = "--{$boundary}--";
		$body[] = "";

		// set options
		@curl_setopt_array($this->curl, [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => implode("\r\n", $body),
			CURLOPT_HTTPHEADER => array_merge([
				"Expect: ",
				"Content-Type: multipart/form-data; boundary={$boundary}", // change Content-Type
			], $extraHeaders)
		]);

		return $this;
	}

	public function setPreprocessor(Preprocessor $preprocessor){
		throw new \RuntimeException("Unsupported operation: setPreprocessor");
	}
}
