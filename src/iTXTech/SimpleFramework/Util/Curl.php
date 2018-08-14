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
 * @author iTXTech
 * @link https://itxtech.org
 *
 */

namespace iTXTech\SimpleFramework\Util;

class Curl{
	protected $curl;
	protected $url;
	protected $content;

	public function __construct(){
		$this->reload();
		return $this;
	}

	public function reload(){
		if(is_resource($this->curl)){
			curl_close($this->curl);
		}
		$this->curl = curl_init();
		if(substr(php_uname(), 0, 7) == "Windows"){
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
		}//Stupid Windows
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		$this->returnHeader(true);
		$this->setTimeout(10);
		return $this;
	}

	public function getUrl(){
		return $this->url;
	}

	public function getContent(){
		return $this->content;
	}

	public function setUA($ua){
		curl_setopt($this->curl, CURLOPT_USERAGENT, $ua);
		return $this;
	}

	public function setUrl($url){
		$this->url = $url;
		curl_setopt($this->curl, CURLOPT_URL, $url);
		return $this;
	}

	public function returnHeader($bool){
		curl_setopt($this->curl, CURLOPT_HEADER, ($bool == true) ? 1 : 0);
		return $this;
	}

	public function returnBody($bool){
		curl_setopt($this->curl, CURLOPT_NOBODY, ($bool == false) ? 1 : 0);
		return $this;
	}

	public function setHeader($arr){
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, $arr);
		return $this;
	}

	public function setCookie($cookies){
		$payload = '';
		foreach($cookies as $key => $cookie){
			$payload .= "$key=$cookie; ";
		}
		curl_setopt($this->curl, CURLOPT_COOKIE, $payload);
		return $this;
	}

	public function setReferer($referer){
		curl_setopt($this->curl, CURLOPT_REFERER, $referer);
		return $this;
	}

	public function setGet($get){
		$payload = '?';
		foreach($get as $key => $content){
			$payload .= urlencode($key) . '=' . urlencode($content) . '&';
		}
		curl_setopt($this->curl, CURLOPT_URL, $this->url . $payload);
		return $this;
	}

	public function setPost($post){
		$payload = '';
		foreach($post as $key => $content){
			$payload .= urlencode($key) . '=' . urlencode($content) . '&';
		}
		curl_setopt($this->curl, CURLOPT_POST, 1);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $payload);
		return $this;
	}

	public function setEncPost($post){
		curl_setopt($this->curl, CURLOPT_POST, 1);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post);
		return $this;
	}

	public function setTimeout($timeout){
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($this->curl, CURLOPT_TIMEOUT, $timeout);
		return $this;
	}

	public function setOpt(int $option, $value){
		curl_setopt($this->curl, $option, $value);
		return $this;
	}

	public function keepCookie(){
		curl_setopt($this->curl, CURLOPT_COOKIEJAR, '');
		curl_setopt($this->curl, CURLOPT_COOKIEFILE, '');
		return $this;
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

	public function isError(){
		return (curl_errno($this->curl)) ? true : false;
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
					continue;
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
}